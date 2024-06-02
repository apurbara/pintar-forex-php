<?php

namespace Company\Infrastructure\Persistence\Doctrine\Repository;

use Company\Domain\Model\Sales\CustomerAssignment;
use Company\Domain\Task\InCompany\CustomerAssignment\CustomerAssignmentRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;

class DoctrineCustomerAssignmentRepository extends DoctrineEntityRepository implements CustomerAssignmentRepository
{

    public function add(CustomerAssignment $customerAssignment): void
    {
        $this->persist($customerAssignment);
    }

    public function ofId(string $id): CustomerAssignment
    {
        return $this->findOneByIdOrDie($id);
    }

    //
    public function aCustomerAssignment(string $id): array
    {
        return $this->queryOneById($id);
    }

    public function customerAssignmentList(array $paginationSchema): array
    {
        return $this->queryPaginationList($paginationSchema);
    }
}
