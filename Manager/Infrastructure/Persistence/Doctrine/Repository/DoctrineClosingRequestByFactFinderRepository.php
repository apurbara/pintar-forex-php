<?php

namespace Manager\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\DBAL\Query\QueryBuilder;
use Manager\Domain\Model\Manager\Sales\FactFindingAssignment\ClosingRequestByFactFinder;
use Manager\Domain\Task\ClosingRequestByFactFinder\ClosingRequestByFactFinderRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrinePaginationListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;

class DoctrineClosingRequestByFactFinderRepository extends DoctrineEntityRepository implements ClosingRequestByFactFinderRepository
{

    public function ofId(string $id): ClosingRequestByFactFinder
    {
        return $this->findOneByIdOrDie($id);
    }

    //

    protected function createCoreQueryBuilder(): QueryBuilder
    {
        return parent::createCoreQueryBuilder()
                        ->innerJoin('ClosingRequestByFactFinder', 'FactFindingAssignment', 'FactFindingAssignment',
                                'ClosingRequestByFactFinder.FactFindingAssignment_id = FactFindingAssignment.id')
                        ->innerJoin('FactFindingAssignment', 'Sales', 'Sales', 'FactFindingAssignment.Sales_id = Sales.id');
    }

    public function aClosingRequestByFactFinderBelongsToManager(string $managerId, string $id): ?array
    {
        $filters = [
            new Filter($managerId, 'Sales.Manager_id'),
            new Filter($id, 'ClosingRequestByFactFinder.id'),
        ];
        return $this->fetchOneBy($filters);
    }

    public function closingRequestByFactFinderListBelongsToManager(string $managerId, array $paginationSchema): array
    {
        $doctrinePaginationListCategory = DoctrinePaginationListCategory::fromSchema($paginationSchema)
                ->addFilter(new Filter($managerId, 'Sales.Manager_id'));
        return $this->fetchPaginationList($doctrinePaginationListCategory);
    }

    public function closingRequestByFactFinderCountBelongsToManager(string $managerId, array $searchSchema)
    {
        $qb = $this->dbalQueryBuilder();
        $qb->select("COUNT(*)")
                ->from('ClosingRequestByFactFinder')
                ->innerJoin('ClosingRequestByFactFinder', 'FactFindingAssignment', 'FactFindingAssignment',
                        'ClosingRequestByFactFinder.FactFindingAssignment_id = FactFindingAssignment.id')
                ->innerJoin('FactFindingAssignment', 'Sales', 'Sales', 'FactFindingAssignment.Sales_id = Sales.id')
                ->andWhere($qb->expr()->eq('Sales.Manager_id', ':managerId'))
                ->setParameter('managerId', $managerId);
        foreach ($searchSchema['filters'] ?? [] as $filterSchema) {
            Filter::fromSchema($filterSchema)->applyToQuery($qb);
        }
        return $qb->executeQuery()->fetchOne();
    }
}
