<?php

namespace Manager\Infrastructure\Persistence\Doctrine\Repository;

use Manager\Domain\Model\Manager;
use Manager\Domain\Task\ManagerRepository;
use Resources\Exception\RegularException;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;

class DoctrineManagerRepository extends DoctrineEntityRepository implements ManagerRepository
{
    
    public function ofEmail(string $email): Manager
    {
        $criteria = [
            'accountInfo.email' => $email,
        ];
        $manager = $this->findOneBy($criteria);
        if (empty($manager)) {
            throw RegularException::unauthorized('account not found');
        }
        return $manager;
    }
    
    public function ofId(string $id): Manager
    {
        return $this->findOneByIdOrDie($id);
    }
}
