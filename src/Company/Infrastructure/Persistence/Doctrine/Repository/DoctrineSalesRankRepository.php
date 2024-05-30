<?php

namespace Company\Infrastructure\Persistence\Doctrine\Repository;

use Company\Domain\Task\InCompany\SalesRank\SalesRankRepository;
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
