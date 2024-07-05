<?php

namespace Sales\Infrastructure\Persistence\Doctrine\Repository;

use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Sales\Domain\Model\Sales;

class DoctrineSalesRepository extends DoctrineEntityRepository
{

    public function ofId(string $id): Sales
    {
        return $this->findOneByIdOrDie($id);
    }
}
