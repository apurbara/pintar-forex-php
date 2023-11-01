<?php

namespace Sales\Infrastructure\Persistence\Doctrine\Repository;

use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Sales\Domain\Model\CustomerVerification;
use Sales\Domain\Task\CustomerVerification\CustomerVerificationRepository;

class DoctrineCustomerVerificationRepository extends DoctrineEntityRepository implements CustomerVerificationRepository
{

    public function ofId(string $id): CustomerVerification
    {
        return $this->findOneByIdOrDie($id);
    }
}
