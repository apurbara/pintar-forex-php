<?php

namespace Sales\Infrastructure\Persistence\Doctrine\Repository;

use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrinePaginationListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\ScheduledSalesActivity;
use Sales\Domain\Task\ScheduledSalesActivity\ScheduledSalesActivityRepository;

class DoctrineScheduledSalesActivityRepository extends DoctrineEntityRepository
        implements ScheduledSalesActivityRepository
{
    
    //
    protected function createCoreQueryBuilder(): \Doctrine\DBAL\Query\QueryBuilder
    {
        $qb = parent::createCoreQueryBuilder();
        $qb->innerJoin('ScheduledSalesActivity', 'AssignedCustomer', 'AssignedCustomer', 'ScheduledSalesActivity.AssignedCustomer_id = AssignedCustomer.id');
        return $qb;
    }

    public function add(ScheduledSalesActivity $scheduledSalesActivity): void
    {
        $this->persist($scheduledSalesActivity);
    }

    public function ofId(string $id): ScheduledSalesActivity
    {
        return $this->findOneByIdOrDie($id);
    }

    public function scheduledSalesActivityBelongsToSalesDetail(string $salesId, string $id): array
    {
        $filters = [
            new Filter($salesId, 'AssignedCustomer.Sales_id'),
            new Filter($id, 'ScheduledSalesActivity.id'),
        ];
        return $this->fetchOneOrDie($filters);
    }

    public function scheduledSalesActivityBelongsToSalesList(string $salesId, array $paginationSchema): array
    {
        $doctrinePaginationListCategory = DoctrinePaginationListCategory::fromSchema($paginationSchema)
                ->addFilter(new Filter($salesId, 'AssignedCustomer.Sales_id'));
        return $this->fetchPaginationList($doctrinePaginationListCategory);
    }
}
