<?php

namespace Manager\Infrastructure\Persistence\Doctrine\Repository;

use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer;
use Manager\Domain\Task\AssignedCustomer\AssignedCustomerRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrinePaginationListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;

class DoctrineAssignedCustomerRepository extends DoctrineEntityRepository implements AssignedCustomerRepository
{

//    protected function createCoreQueryBuilder(): QueryBuilder
//    {
//        return $this->dbalQueryBuilder()
//                        ->select('*')
//                        ->from('AssignedCustomer');
//    }

    public function add(AssignedCustomer $assignedCustomer): void
    {
        $this->persist($assignedCustomer);
    }

    public function assignedCustomerListManagedByManager(string $managerId, array $paginationSchema): array
    {
        $qb = $this->createCoreQueryBuilder()
                ->innerJoin('AssignedCustomer', 'Sales', 'Sales', 'AssignedCustomer.Sales_id = Sales.id');
        return $doctrinePaginationListCategory = DoctrinePaginationListCategory::fromSchema($paginationSchema)
                ->addFilter(new Filter($managerId, 'Sales.Manager_id'))
                ->paginateResult($qb, 'AssignedCustomer');
    }

    public function anAssignedCustomerManagedByManager(string $managerId, string $id): array
    {
        $qb = $this->createCoreQueryBuilder();
        $qb->leftJoin('AssignedCustomer', 'Sales', 'Sales', 'AssignedCustomer.Sales_id = Sales.id')
                ->setMaxResults(1)
                ->andWhere($qb->expr()->eq('Sales.Manager_id', ':managerId'))
                ->setParameter('managerId', $managerId)
                ->andWhere($qb->expr()->eq('AssignedCustomer.id', ':id'))
                ->setParameter('id', $id);
        return $qb->executeQuery()->fetchAssociative() ?: [];
    }
}
