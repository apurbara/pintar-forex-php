<?php

namespace Sales\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\DBAL\Query\QueryBuilder;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrinePaginationListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;
use Sales\Domain\Model\Sales\CustomerAssignment;
use Sales\Domain\Task\BySales\CustomerAssignment\CustomerAssignmentRepository;

class DoctrineCustomerAssignmentRepository extends DoctrineEntityRepository implements CustomerAssignmentRepository
{

    public function add(CustomerAssignment $customerAssignment): void
    {
        $this->persist($customerAssignment);
    }

    public function ofId(string $id): CustomerAssignment
    {
        return $this->findOneByIdOrDie($id);
    }

    protected function createCoreQueryBuilder(): QueryBuilder
    {
        return parent::createCoreQueryBuilder()
            ->innerJoin('CustomerAssignment', 'Customer', 'Customer', 'CustomerAssignment.Customer_id = Customer.id');
    }

    public function aCustomerAssignmentBelongsToSales(string $salesId, string $id): array
    {
        $filters = [
            new Filter($salesId, 'CustomerAssignment.Sales_id'),
            new Filter($id, 'CustomerAssignment.id'),
        ];
        return $this->fetchOneBy($filters);
    }

    public function customerAssignmentListBelongsToSales(string $salesId, array $pageSchema): array
    {
        $doctrinePaginationListCategory = DoctrinePaginationListCategory::fromSchema($pageSchema)
                ->addFilter(new Filter($salesId, 'CustomerAssignment.Sales_id'));
        return $this->fetchPaginationList($doctrinePaginationListCategory);
    }
    
    public function totalCustomerAssignmentBelongsToSales(string $salesId, array $searchSchema): int
    {
        $qb = $this->dbalQueryBuilder();
        $qb->select('COUNT(CustomerAssignment.id)')
                ->from('CustomerAssignment')
                ->andWhere('CustomerAssignment.Sales_id = :salesId')
                ->setParameter('salesId', $salesId);
        
        foreach ($searchSchema['filters'] ?? [] as $filterSchema) {
            Filter::fromSchema($filterSchema)->applyToQuery($qb);
        }
        
        return $qb->executeQuery()->fetchOne();
    }
}
