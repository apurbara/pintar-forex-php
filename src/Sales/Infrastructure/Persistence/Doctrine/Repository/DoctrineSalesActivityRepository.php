<?php

namespace Sales\Infrastructure\Persistence\Doctrine\Repository;

use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Sales\Domain\Model\SalesActivity;
use Sales\Domain\Task\SalesActivity\SalesActivityRepository;

class DoctrineSalesActivityRepository extends DoctrineEntityRepository implements SalesActivityRepository
{

    public function ofId(string $id): SalesActivity
    {
        return $this->findOneByIdOrDie($id);
    }
}
