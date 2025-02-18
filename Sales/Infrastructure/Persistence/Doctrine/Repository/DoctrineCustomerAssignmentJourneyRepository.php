<?php

namespace Sales\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\DBAL\Query\QueryBuilder;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrinePaginationListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;
use Sales\Domain\Task\CustomerAssignmentJourney\CustomerAssignmentJourneyRepository;

class DoctrineCustomerAssignmentJourneyRepository extends DoctrineEntityRepository
        implements CustomerAssignmentJourneyRepository
{

    protected function createCoreQueryBuilder(): QueryBuilder
    {
        return parent::createCoreQueryBuilder()
                        ->innerJoin('CustomerAssignmentJourney', 'CustomerAssignment', 'CustomerAssignment',
                                'CustomerAssignmentJourney.CustomerAssignment_id = CustomerAssignment.id');
    }

    public function customerAssignmentJourneyListBelongsToSales(string $salesId, array $paginationSchema): array
    {
        $doctrinePaginationListCategory = DoctrinePaginationListCategory::fromSchema($paginationSchema)
                ->addFilter(new Filter($salesId, 'CustomerAssignment.Sales_id'));
        return $this->fetchPaginationList($doctrinePaginationListCategory);
    }
}
