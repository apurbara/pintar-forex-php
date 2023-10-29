<?php

namespace User\Infrastructure\Persistence\Doctrine\Repository;

use Resources\Exception\RegularException;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use User\Application\Service\Guest\PersonnelRepository;
use User\Application\Service\Personnel\PersonnelRepository as PersonnelRepository2;
use User\Domain\Model\Personnel;

class DoctrinePersonnelRepository extends DoctrineEntityRepository implements PersonnelRepository, PersonnelRepository2
{

    public function ofEmail(string $email): Personnel
    {
        $result = $this->findOneBy([
            'accountInfo.email' => $email,
        ]);
        if (empty($result)) {
            throw RegularException::notFound('account not found');
        }
        return $result;
    }

    public function ofId(string $id): Personnel
    {
        return $this->findOneByIdOrDie($id);
    }
}
