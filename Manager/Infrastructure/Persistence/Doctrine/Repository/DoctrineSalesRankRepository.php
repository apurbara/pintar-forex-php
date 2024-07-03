<?php

namespace Manager\Infrastructure\Persistence\Doctrine\Repository;

use Manager\Domain\Task\Dependency\SalesRankRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;

class DoctrineSalesRankRepository extends DoctrineEntityRepository implements SalesRankRepository
{

    public function allActive(): array
    {
        return $this->findBy([
                    'disabled' => false,
        ]);
    }
}
