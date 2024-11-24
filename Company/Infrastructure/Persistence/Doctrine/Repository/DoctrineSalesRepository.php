<?php

namespace Company\Infrastructure\Persistence\Doctrine\Repository;

use Company\Domain\Model\Manager\Sales;
use Company\Domain\Service\SalesRepository as SalesRepository2;
use Company\Domain\Task\Sales\SalesRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;
use Shared\Domain\Enum\CustomerAssignmentStatus;
use Shared\Domain\Enum\SalesRole;

class DoctrineSalesRepository extends DoctrineEntityRepository implements SalesRepository, SalesRepository2
{

    public function add(Sales $sales): void
    {
        $this->persist($sales);
    }

    public function ofId(string $id): Sales
    {
        return $this->findOneByIdOrDie($id);
    }

    public function isEmailAvailable(string $email): bool
    {
        $filters = [
            new Filter($email, 'Sales.email'),
            new Filter(false, 'Sales.contractTerminated'),
        ];
        return empty($this->fetchOneBy($filters));
    }
    
    public function findLeastOccupiedFactFinderBelongsToManager(string $managerId): ?Sales
    {
        $activeAssignmentStatus = CustomerAssignmentStatus::ACTIVE->value;
        $factFinderRole = SalesRole::FACT_FINDER->value;
        $sql = <<<_SQL
SELECT Sales.id, Sales.contractTerminated, Sales.role, _a.activeAssignmentCount
FROM Sales
    LEFT JOIN (
        SELECT COUNT(FactFindingAssignment.id) activeAssignmentCount, FactFindingAssignment.Sales_id
        FROM FactFindingAssignment
        WHERE FactFindingAssignment.status = '{$activeAssignmentStatus}'
        GROUP BY FactFindingAssignment.Sales_id
    )_a ON _a.Sales_id = Sales.id
WHERE Sales.Manager_id = :managerId
    AND Sales.role = '{$factFinderRole}'
    AND Sales.contractTerminated = 0
ORDER BY activeAssignmentCount ASC
LIMIT 1
_SQL;
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addEntityResult(Sales::class, 'sales')
                ->addFieldResult('sales', 'id', 'id')
                ->addFieldResult('sales', 'contractTerminated', 'contractTerminated')
                ->addFieldResult('sales', 'role', 'role');
        
        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm)
                ->setParameter('managerId', $managerId);
        $result = $query->getResult();
        return empty($result) ? null : $result[0];
    }

    public function findLeastOccupiedStrikerBelongsToManager(string $managerId): ?Sales
    {
        $activeAssignmentStatus = CustomerAssignmentStatus::ACTIVE->value;
        $strikerRole = SalesRole::STRIKER->value;
        $sql = <<<_SQL
SELECT Sales.id, Sales.contractTerminated, Sales.role, _a.activeAssignmentCount
FROM Sales
    LEFT JOIN (
        SELECT COUNT(StrikingAssignment.id) activeAssignmentCount, StrikingAssignment.Sales_id
        FROM StrikingAssignment
        WHERE StrikingAssignment.status = '{$activeAssignmentStatus}'
        GROUP BY StrikingAssignment.Sales_id
    )_a ON _a.Sales_id = Sales.id
WHERE Sales.Manager_id = :managerId
    AND Sales.role = '{$strikerRole}'
    AND Sales.contractTerminated = 0
ORDER BY activeAssignmentCount ASC
LIMIT 1
_SQL;
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addEntityResult(Sales::class, 'sales')
                ->addFieldResult('sales', 'id', 'id')
                ->addFieldResult('sales', 'contractTerminated', 'contractTerminated')
                ->addFieldResult('sales', 'role', 'role');
        
        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm)
                ->setParameter('managerId', $managerId);
        $result = $query->getResult();
        return empty($result) ? null : $result[0];
    }

    //

    public function aSales(string $id)
    {
        return $this->queryOneById($id);
    }

    public function salesList(array $paginationSchema): array
    {
        return $this->queryPaginationList($paginationSchema);
    }

    public function allSales(array $searchSchema)
    {
        return $this->queryAllList($searchSchema);
    }
}
