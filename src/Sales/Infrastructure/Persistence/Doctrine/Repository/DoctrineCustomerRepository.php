<?php

namespace Sales\Infrastructure\Persistence\Doctrine\Repository;

use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;
use Sales\Domain\Task\Customer\CustomerRepository;

class DoctrineCustomerRepository extends DoctrineEntityRepository implements CustomerRepository
{

    public function isEmailAvailable(string $email): bool
    {
        $filters = [new Filter($email, 'Customer.email')];
        return empty($this->fetchOneBy($filters));
    }
}
