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
}
