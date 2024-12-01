<?php

namespace Manager\Infrastructure\Persistence\Doctrine\Repository;

use DateTime;
use Doctrine\DBAL\Query\QueryBuilder;
use Manager\Domain\Model\Manager\Sales\StrikingAssignment\ClosingRequest;
use Manager\Domain\Task\ClosingRequest\ClosingRequestRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineAllListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrinePaginationListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;
use Shared\Domain\Enum\ManagementApprovalStatus;

class DoctrineClosingRequestRepository extends DoctrineEntityRepository implements ClosingRequestRepository
{

    public function ofId(string $id): ClosingRequest
    {
        return $this->findOneByIdOrDie($id);
    }

    //

    protected function createCoreQueryBuilder(): QueryBuilder
    {
        return parent::createCoreQueryBuilder()
                        ->innerJoin('ClosingRequest', 'StrikingAssignment', 'StrikingAssignment',
                                'ClosingRequest.StrikingAssignment_id = StrikingAssignment.id')
                        ->innerJoin('StrikingAssignment', 'Sales', 'Sales', 'StrikingAssignment.Sales_id = Sales.id');
    }

    public function aClosingRequestBelongsToManager(string $managerId, string $id): ?array
    {
        $filters = [
            new Filter($managerId, 'Sales.Manager_id'),
            new Filter($id, 'ClosingRequest.id'),
        ];
        return $this->fetchOneBy($filters);
    }

    public function closingRequestListBelongsToManager(string $managerId, array $paginationSchema): array
    {
        $doctrinePaginationListCategory = DoctrinePaginationListCategory::fromSchema($paginationSchema)
                ->addFilter(new Filter($managerId, 'Sales.Manager_id'));
        return $this->fetchPaginationList($doctrinePaginationListCategory);
    }

    public function closingRequestCountBelongsToManager(string $managerId, array $searchSchema)
    {
        $qb = $this->dbalQueryBuilder();
        $qb->select("COUNT(*)")
                ->from('ClosingRequest')
                ->innerJoin('ClosingRequest', 'StrikingAssignment', 'StrikingAssignment',
                        'ClosingRequest.StrikingAssignment_id = StrikingAssignment.id')
                ->innerJoin('StrikingAssignment', 'Sales', 'Sales', 'StrikingAssignment.Sales_id = Sales.id')
                ->andWhere($qb->expr()->eq('Sales.Manager_id', ':managerId'))
                ->setParameter('managerId', $managerId);
        foreach ($searchSchema['filters'] ?? [] as $filterSchema) {
            Filter::fromSchema($filterSchema)->applyToQuery($qb);
        }
        return $qb->executeQuery()->fetchOne();
    }
}
