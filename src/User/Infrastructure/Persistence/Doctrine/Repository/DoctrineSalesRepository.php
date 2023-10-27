<?php

namespace User\Infrastructure\Persistence\Doctrine\Repository;

use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;
use User\Domain\Task\ByPersonnel\Sales\SalesRepository;

class DoctrineSalesRepository extends DoctrineEntityRepository implements SalesRepository
{

    public function activeSalesAssignmentBelongsToPersonnel(string $personnelId): ?array
    {
        $filters = [
            new Filter($personnelId, 'Sales.Personnel_id'),
            new Filter(false, 'Sales.disabled'),
        ];
        $result = $this->fetchOneBy($filters);
        return $result ? $result : null;
    }
}
