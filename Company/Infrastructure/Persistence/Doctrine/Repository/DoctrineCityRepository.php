<?php

namespace Company\Infrastructure\Persistence\Doctrine\Repository;

use Company\Domain\Model\Province\City;
use Company\Domain\Task\City\CityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;

class DoctrineCityRepository extends DoctrineEntityRepository implements CityRepository
{

    public function add(City $city): void
    {
        $this->persist($city);
    }

    public function ofId(string $id): City
    {
        return $this->findOneByIdOrDie($id);
    }

    //
    public function cityList(array $paginationSchema)
    {
        return $this->queryPaginationList($paginationSchema);
    }

    public function allCity(array $searchSchema)
    {
        return $this->queryAllList($searchSchema);
    }
}
