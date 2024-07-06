<?php

namespace Sales\Infrastructure\Persistence\Doctrine\Repository;

use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Sales\Domain\Model\Sales;
use Sales\Domain\Task\SalesRepository;

class DoctrineSalesRepository extends DoctrineEntityRepository implements SalesRepository
{

    public function ofId(string $id): Sales
    {
        return $this->findOneByIdOrDie($id);
    }
}
