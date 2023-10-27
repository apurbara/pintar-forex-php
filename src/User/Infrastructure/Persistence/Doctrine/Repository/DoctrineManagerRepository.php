<?php

namespace User\Infrastructure\Persistence\Doctrine\Repository;

use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineAllListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;
use User\Domain\Task\ByPersonnel\Manager\ManagerRepository;

class DoctrineManagerRepository extends DoctrineEntityRepository implements ManagerRepository
{

    public function allManagerListBelongsToPersonnel(string $personnelId, array $listSchema): array
    {
        $doctrineAllListCategory = DoctrineAllListCategory::fromSchema($listSchema)
                ->addFilter(new Filter($personnelId, 'Manager.Personnel_id'));
        return $this->fetchAllList($doctrineAllListCategory);
    }

    public function activeManagerAssignmentBelongsToPersonnel(string $personnelId): ?array
    {
        $filters = [
            new Filter($personnelId, 'Sales.Personnel_id'),
            new Filter(false, 'Sales.disabled'),
        ];
        return $this->fetchOneBy($filters);
    }
}
