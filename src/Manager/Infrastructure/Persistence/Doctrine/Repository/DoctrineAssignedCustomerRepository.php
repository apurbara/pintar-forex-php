<?php

namespace Manager\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\DBAL\Query\QueryBuilder;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer;
use Manager\Domain\Task\AssignedCustomer\AssignedCustomerRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;

class DoctrineAssignedCustomerRepository extends DoctrineEntityRepository implements AssignedCustomerRepository
{

    protected function createCoreQueryBuilder(): QueryBuilder
    {
        return $this->dbalQueryBuilder()
                        ->select('*')
                        ->from('AssignedCustomer');
    }

    public function add(AssignedCustomer $assignedCustomer): void
    {
        $this->persist($assignedCustomer);
    }
}
