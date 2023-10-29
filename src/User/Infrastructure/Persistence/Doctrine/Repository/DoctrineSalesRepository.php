<?php

namespace User\Infrastructure\Persistence\Doctrine\Repository;

use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrinePaginationListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;
use User\Domain\Task\ByPersonnel\Sales\SalesRepository;

class DoctrineSalesRepository extends DoctrineEntityRepository implements SalesRepository
{

    public function salesAssignmentListBelongsToPersonnel(string $personnelId, array $paginationSchema): ?array
    {
        $doctrinePaginationListCategory = DoctrinePaginationListCategory::fromSchema($paginationSchema)
                ->addFilter(new Filter($personnelId, 'Sales.Personnel_id'));
        return $this->fetchPaginationList($doctrinePaginationListCategory);
    }
}
