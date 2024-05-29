<?php

namespace Sales\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\DBAL\Query\QueryBuilder;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrinePaginationListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;
use Sales\Domain\Model\Sales\CustomerAssignment\ClosingRequest;
use Sales\Domain\Task\BySales\ClosingRequest\ClosingRequestRepository;

class DoctrineClosingRequestRepository extends DoctrineEntityRepository implements ClosingRequestRepository
{

    public function add(ClosingRequest $closingRequest): void
    {
        $this->persist($closingRequest);
    }

    public function ofId(string $id): ClosingRequest
    {
        return $this->findOneByIdOrDie($id);
    }

    //
    protected function createCoreQueryBuilder(): QueryBuilder
    {
        return parent::createCoreQueryBuilder()
                        ->innerJoin('ClosingRequest', 'CustomerAssignment', 'CustomerAssignment',
                                'ClosingRequest.CustomerAssignment_id = CustomerAssignment.id');
    }

    public function closingRequestListBelongsToSales(string $salesId, array $paginationSchema): array
    {
        $doctrinePaginationListCategory = DoctrinePaginationListCategory::fromSchema($paginationSchema)
                ->addFilter(new Filter($salesId, 'CustomerAssignment.Sales_id'));
        return $this->fetchPaginationList($doctrinePaginationListCategory);
    }

    public function aClosingRequestBelongsToSales(string $salesId, string $id): array
    {
        $filters = [
            new Filter($salesId, 'CustomerAssignment.Sales_id'),
            new Filter($id, 'ClosingRequest.id'),
        ];
        return $this->fetchOneOrDie($filters);
    }
}
