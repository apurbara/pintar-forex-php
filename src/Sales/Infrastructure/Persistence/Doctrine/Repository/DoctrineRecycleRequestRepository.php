<?php

namespace Sales\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\DBAL\Query\QueryBuilder;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrinePaginationListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\RecycleRequest;
use Sales\Domain\Task\RecycleRequest\RecycleRequestRepository;

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
                        ->innerJoin('RecycleRequest', 'AssignedCustomer', 'AssignedCustomer',
                                'RecycleRequest.AssignedCustomer_id = AssignedCustomer.id');
    }

    public function recycleRequestListBelongsToSales(string $salesId, array $paginationSchema): array
    {
        $doctrinePaginationListCategory = DoctrinePaginationListCategory::fromSchema($paginationSchema)
                ->addFilter(new Filter($salesId, 'AssignedCustomer.Sales_id'));
        return $this->fetchPaginationList($doctrinePaginationListCategory);
    }

    public function aRecycleRequestBelongsToSales(string $salesId, string $id): array
    {
        $filters = [
            new Filter($salesId, 'AssignedCustomer.Sales_id'),
            new Filter($id, 'RecycleRequest.id'),
        ];
        return $this->fetchOneOrDie($filters);
    }
}
