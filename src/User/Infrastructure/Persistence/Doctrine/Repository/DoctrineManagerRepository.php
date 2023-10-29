<?php

namespace User\Infrastructure\Persistence\Doctrine\Repository;

use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrinePaginationListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;
use User\Domain\Task\ByPersonnel\Manager\ManagerRepository;

class DoctrineManagerRepository extends DoctrineEntityRepository implements ManagerRepository
{

    public function managerAssignmentListBelongsToPersonnel(string $personnelId, array $paginationSchema): ?array
    {
        $doctrinePaginationListCategory = DoctrinePaginationListCategory::fromSchema($paginationSchema)
                ->addFilter(new Filter($personnelId, 'Manager.Personnel_id'));
        return $this->fetchPaginationList($doctrinePaginationListCategory);
    }
}
