<?php

namespace Sales\Infrastructure\Persistence\Doctrine\Repository;

use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Sales\Domain\DependencyModel\AreaStructure\Area;
use Sales\Domain\Task\Dependency\AreaRepository;

class DoctrineAreaRepository extends DoctrineEntityRepository implements AreaRepository
{

    public function ofId(string $id): Area
    {
        return $this->findOneByIdOrDie($id);
    }
}
