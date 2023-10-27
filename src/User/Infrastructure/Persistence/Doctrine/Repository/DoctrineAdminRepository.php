<?php

namespace User\Infrastructure\Persistence\Doctrine\Repository;

use Resources\Exception\RegularException;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use User\Application\Service\Guest\AdminRepository;
use User\Domain\Model\Admin;

class DoctrineAdminRepository extends DoctrineEntityRepository implements AdminRepository
{
    
    public function ofEmail(string $email): Admin
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
