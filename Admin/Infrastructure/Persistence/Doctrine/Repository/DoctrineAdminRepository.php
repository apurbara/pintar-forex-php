<?php

namespace Admin\Infrastructure\Persistence\Doctrine\Repository;

use Admin\Domain\Model\Admin;
use Resources\Exception\RegularException;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;

class DoctrineAdminRepository extends DoctrineEntityRepository
{

    public function ofEmail(string $email): Admin
    {
        $admin = $this->findOneBy([
            "accountInfo.email" => $email,
        ]);
        if (empty($admin)) {
            throw RegularException::unauthorized('inactive account or invalid email and password');
        }
        return $admin;
    }

    public function ofId(string $id): Admin
    {
        return $this->findOneByIdOrDie($id);
    }
}
