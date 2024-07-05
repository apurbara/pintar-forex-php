<?php

namespace Sales\Infrastructure\Persistence\Doctrine\Repository;

use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Sales\Domain\DependencyModel\Province\City;
use Sales\Domain\Task\Dependency\CityRepository;

class DoctrineCityRepository extends DoctrineEntityRepository implements CityRepository
{

    public function ofId(string $id): City
    {
        return $this->findOneByIdOrDie($id);
    }
}
