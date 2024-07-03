<?php

namespace Manager\Infrastructure\Persistence\Doctrine\Repository;

use Manager\Domain\Model\Manager;
use Manager\Domain\Task\ManagerRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;

class DoctrineManagerRepository extends DoctrineEntityRepository implements ManagerRepository
{
    
    public function ofId(string $id): Manager
    {
        return $this->findOneByIdOrDie($id);
    }
}
