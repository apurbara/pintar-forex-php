<?php

namespace Manager\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\DBAL\Query\QueryBuilder;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;

class DoctrineAssignedCustomerRepository extends DoctrineEntityRepository
{
    protected function createCoreQueryBuilder(): QueryBuilder
    {
        return $this->dbalQueryBuilder()
                ->select('*')
                ->from('AssignedCustomer');
    }
}
