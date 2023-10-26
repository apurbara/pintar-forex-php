<?php

namespace Sales\Infrastructure\Persistence\Doctrine\Repository;

use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Sales\Domain\Model\AreaStructure\Area;
use Sales\Domain\Task\Area\AreaRepository;

class DoctrineAreaRepository extends DoctrineEntityRepository implements AreaRepository
{

    public function ofId(string $id): Area
    {
        return $this->findOneByIdOrDie($id);
    }
}
