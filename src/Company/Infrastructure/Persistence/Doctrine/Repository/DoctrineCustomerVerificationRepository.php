<?php

namespace Company\Infrastructure\Persistence\Doctrine\Repository;

use Company\Domain\Model\CustomerVerification;
use Company\Domain\Task\InCompany\CustomerVerification\CustomerVerificationRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrinePaginationListCategory;

class DoctrineCustomerVerificationRepository extends DoctrineEntityRepository implements CustomerVerificationRepository
{

    public function add(CustomerVerification $customerVerification): void
    {
        $this->persist($customerVerification);
    }

    public function customerVerificationDetail(string $id): array
    {
        return $this->fetchOneByIdOrDie($id);
    }

    public function customerVerificationList(array $paginationSchema): array
    {
        $doctrinePaginationListCategory = DoctrinePaginationListCategory::fromSchema($paginationSchema);
        return $this->fetchPaginationList($doctrinePaginationListCategory);
    }
}
