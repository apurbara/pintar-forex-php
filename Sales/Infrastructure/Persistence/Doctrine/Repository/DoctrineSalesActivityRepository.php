<?php

namespace Sales\Infrastructure\Persistence\Doctrine\Repository;

use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Sales\Domain\DependencyModel\SalesActivity;
use Sales\Domain\Task\Dependency\SalesActivityRepository;

class DoctrineSalesActivityRepository extends DoctrineEntityRepository implements SalesActivityRepository
{

    public function ofId(string $id): SalesActivity
    {
        return $this->findOneByIdOrDie($id);
    }

    public function anInitialSalesActivity(): ?SalesActivity
    {
        return $this->findOneBy([
                    'initial' => true,
                    'disabled' => false,
        ]);
    }
}
