<?php

namespace Sales\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\DBAL\Query\QueryBuilder;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrinePaginationListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;
use Sales\Domain\Model\Sales\CustomerAssignment\RecycleRequest;
use Sales\Domain\Task\BySales\RecycleRequest\RecycleRequestRepository;

class DoctrineRecycleRequestRepository extends DoctrineEntityRepository implements RecycleRequestRepository
{

    public function add(RecycleRequest $recycleRequest): void
    {
        $this->persist($recycleRequest);
    }

    public function ofId(string $id): RecycleRequest
    {
        return $this->findOneByIdOrDie($id);
    }

    //
    protected function createCoreQueryBuilder(): QueryBuilder
    {
        return parent::createCoreQueryBuilder()
                        ->innerJoin('RecycleRequest', 'CustomerAssignment', 'CustomerAssignment',
                                'RecycleRequest.CustomerAssignment_id = CustomerAssignment.id');
    }

    public function recycleRequestListBelongsToSales(string $salesId, array $paginationSchema): array
    {
        $doctrinePaginationListCategory = DoctrinePaginationListCategory::fromSchema($paginationSchema)
                ->addFilter(new Filter($salesId, 'CustomerAssignment.Sales_id'));
        return $this->fetchPaginationList($doctrinePaginationListCategory);
    }

    public function aRecycleRequestBelongsToSales(string $salesId, string $id): array
    {
        $filters = [
            new Filter($salesId, 'CustomerAssignment.Sales_id'),
            new Filter($id, 'RecycleRequest.id'),
        ];
        return $this->fetchOneOrDie($filters);
    }
}
