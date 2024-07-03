<?php

namespace Company\Infrastructure\Persistence\Doctrine\Repository;

use Company\Domain\Model\Province;
use Company\Domain\Task\Province\ProvinceRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;

class DoctrineProvinceRepository extends DoctrineEntityRepository implements ProvinceRepository
{

    public function add(Province $province): void
    {
        $this->persist($province);
    }

    public function ofId(string $id): Province
    {
        return $this->findOneByIdOrDie($id);
    }

    //
    public function provinceList(array $paginationSchema)
    {
        return $this->queryPaginationList($paginationSchema);
    }

    public function allProvince(array $searchSchema)
    {
        return $this->queryAllList($searchSchema);
    }
}
