<?php

namespace Manager\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\DBAL\Query\QueryBuilder;
use Manager\Domain\Model\Manager\Sales;
use Manager\Domain\Task\Sales\SalesRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineAllListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrinePaginationListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;

class DoctrineSalesRepository extends DoctrineEntityRepository implements SalesRepository
{

    public function ofId(string $id): Sales
    {
        return $this->findOneByIdOrDie($id);
    }

    //
    protected function createManagerAggregateCoreQueryBuilder(string $managerId): QueryBuilder
    {
        $qb = $this->createCoreQueryBuilder();
        return $qb->andWhere($qb->expr()->eq('Sales.Manager_id', ':managerId'))
                        ->setParameter('managerId', $managerId);
    }

    public function aSalesBelongsToManager(string $managerId, string $id)
    {
        $filters = [new Filter($id, 'Sales.id')];
        return $this->retrieveOne($this->createManagerAggregateCoreQueryBuilder($managerId), $filters);
    }

    public function salesListBelongsToManager(string $managerId, array $paginationSchema)
    {
        return DoctrinePaginationListCategory::fromSchema($paginationSchema)
                        ->paginateResult($this->createManagerAggregateCoreQueryBuilder($managerId),
                                $this->getTableName());
    }

    public function allSalesBelongsToManager(string $managerId, array $searchSchema)
    {
        return DoctrineAllListCategory::fromSchema($searchSchema)
                        ->fetchResult($this->createManagerAggregateCoreQueryBuilder($managerId));
    }
}
