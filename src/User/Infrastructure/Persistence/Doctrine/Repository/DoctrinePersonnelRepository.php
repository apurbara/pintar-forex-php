<?php

namespace User\Infrastructure\Persistence\Doctrine\Repository;

use Resources\Exception\RegularException;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use User\Application\Service\Guest\PersonnelRepository;
use User\Domain\Model\Personnel;

class DoctrinePersonnelRepository extends DoctrineEntityRepository implements PersonnelRepository
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
}
