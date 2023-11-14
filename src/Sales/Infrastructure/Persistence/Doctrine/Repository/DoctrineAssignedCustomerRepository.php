<?php

namespace Sales\Infrastructure\Persistence\Doctrine\Repository;

use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrinePaginationListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;
use Sales\Domain\Task\AssignedCustomer\AssignedCustomerRepository;

class DoctrineAssignedCustomerRepository extends DoctrineEntityRepository implements AssignedCustomerRepository
{

    public function add(AssignedCustomer $assignedCustomer): void
    {
        $this->persist($assignedCustomer);
    }

    public function assignedCustomerToSalesDetail(string $salesId, string $id): array
    {
        $filters = [
            new Filter($salesId, 'AssignedCustomer.Sales_id'),
            new Filter($id, 'AssignedCustomer.id'),
        ];
        return $this->fetchOneBy($filters);
    }

    public function assignedCustomerToSalesList(string $salesId, array $pageSchema): array
    {
        $doctrinePaginationListCategory = DoctrinePaginationListCategory::fromSchema($pageSchema)
                ->addFilter(new Filter($salesId, 'AssignedCustomer.Sales_id'));
        return $this->fetchPaginationList($doctrinePaginationListCategory);
    }

    public function ofId(string $id): AssignedCustomer
    {
        return $this->findOneByIdOrDie($id);
    }

    public function totalCustomerAssignment(string $salesId, array $searchSchema): int
    {
        $qb = $this->dbalQueryBuilder();
        $qb->select('COUNT(AssignedCustomer.id)')
                ->from('AssignedCustomer')
                ->andWhere('AssignedCustomer.Sales_id = :salesId')
                ->setParameter('salesId', $salesId);
        
        foreach ($searchSchema['filters'] ?? [] as $filterSchema) {
            Filter::fromSchema($filterSchema)->applyToQuery($qb);
        }
        
        return $qb->executeQuery()->fetchOne();
    }
}
