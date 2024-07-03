<?php

namespace Company\Infrastructure\Persistence\Doctrine\Repository;

use Company\Domain\Model\Admin;
use Company\Domain\Task\Admin\AdminRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;

class DoctrineAdminRepository extends DoctrineEntityRepository implements AdminRepository
{

    public function ofId(string $id): Admin
    {
        return $this->findOneByIdOrDie($id);
    }
}
