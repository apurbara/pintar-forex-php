<?php

namespace Manager\Infrastructure\Persistence\Doctrine\Repository;

use Manager\Domain\Task\Sales\SalesRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrinePaginationListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;

class DoctrineSalesRepository extends DoctrineEntityRepository implements SalesRepository
{

    public function aSalesManagedByManager(string $managerId, string $id): array
    {
        return $this->fetchOneOrDie([
                    new Filter($managerId, 'Sales.Manager_id'),
                    new Filter($id, 'Sales.id'),
        ]);
    }

    public function salesListManagedByManager(string $managerId, array $paginationSchema): array
    {
        $doctrinePaginationListCategory = DoctrinePaginationListCategory::fromSchema($paginationSchema)
                ->addFilter(new Filter($managerId, 'Sales.Manager_id'));
        return $this->fetchPaginationList($doctrinePaginationListCategory);
    }
}
