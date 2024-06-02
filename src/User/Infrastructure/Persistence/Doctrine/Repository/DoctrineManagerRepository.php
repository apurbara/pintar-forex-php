<?php

namespace User\Infrastructure\Persistence\Doctrine\Repository;

use Resources\Exception\RegularException;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use User\Application\Service\Guest\ManagerRepository as ManagerRepository2;
use User\Application\Service\Manager\ManagerRepository;
use User\Domain\Model\Manager;

class DoctrineManagerRepository extends DoctrineEntityRepository implements ManagerRepository2, ManagerRepository
{

    public function ofId(string $id): Manager
    {
        return $this->findOneByIdOrDie($id);
    }

    public function ofEmail(string $email): Manager
    {
        $result = $this->findOneBy([
            'accountInfo.email' => $email,
        ]);
        if (empty($result)) {
            throw RegularException::notFound('account not found');
        }
        return $result;
    }
}
