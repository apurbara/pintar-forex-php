<?php

namespace Sales\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\DBAL\Query\QueryBuilder;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrinePaginationListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;
use Sales\Domain\Model\Sales\FactFindingAssignment\ClosingRequestByFactFinder;
use Sales\Domain\Task\ClosingRequestByFactFinder\ClosingRequestByFactFinderRepository;

class DoctrineClosingRequestByFactFinderRepository extends DoctrineEntityRepository implements ClosingRequestByFactFinderRepository
{

    public function add(ClosingRequestByFactFinder $closingRequestByFactFinder): void
    {
        $this->persist($closingRequestByFactFinder);
    }

    public function ofId(string $id): ClosingRequestByFactFinder
    {
        return $this->findOneByIdOrDie($id);
    }

    //
    protected function createCoreQueryBuilder(): QueryBuilder
    {
        return parent::createCoreQueryBuilder()
                        ->innerJoin('ClosingRequestByFactFinder', 'FactFindingAssignment', 'FactFindingAssignment',
                                'ClosingRequestByFactFinder.FactFindingAssignment_id = FactFindingAssignment.id');
    }

    public function closingRequestByFactFinderListBelongsToSales(string $salesId, array $paginationSchema): array
    {
        $doctrinePaginationListCategory = DoctrinePaginationListCategory::fromSchema($paginationSchema)
                ->addFilter(new Filter($salesId, 'FactFindingAssignment.Sales_id'));
        return $this->fetchPaginationList($doctrinePaginationListCategory);
    }

    public function aClosingRequestByFactFinderBelongsToSales(string $salesId, string $id): array
    {
        $filters = [
            new Filter($salesId, 'FactFindingAssignment.Sales_id'),
            new Filter($id, 'ClosingRequestByFactFinder.id'),
        ];
        return $this->fetchOneOrDie($filters);
    }
}
