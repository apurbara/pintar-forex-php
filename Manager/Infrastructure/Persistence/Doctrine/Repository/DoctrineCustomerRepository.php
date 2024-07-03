<?php

namespace Manager\Infrastructure\Persistence\Doctrine\Repository;

use Manager\Domain\DependencyModel\Customer;
use Manager\Domain\Task\Dependency\CustomerRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;

class DoctrineCustomerRepository extends DoctrineEntityRepository implements CustomerRepository
{

    public function ofId(string $id): Customer
    {
        return $this->findOneByIdOrDie($id);
    }
}
