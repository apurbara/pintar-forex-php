<?php

namespace Company\Infrastructure\Persistence\Doctrine\Repository;

use Company\Domain\Model\Personnel\Manager;
use Company\Domain\Task\InCompany\Personnel\Manager\ManagerRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrinePaginationListCategory;

class DoctrineManagerRepository extends DoctrineEntityRepository implements ManagerRepository
{

    public function add(Manager $manager): void
    {
        $this->persist($manager);
    }

    public function managerDetail(string $id): array
    {
        return $this->fetchOneByIdOrDie($id);
    }

    public function managerList(array $paginationSchema): array
    {
        $doctrinePaginationListCategory = DoctrinePaginationListCategory::fromSchema($paginationSchema);
        return $this->fetchPaginationList($doctrinePaginationListCategory);
    }

    public function ofId(string $id): Manager
    {
        return $this->findOneByIdOrDie($id);
    }
}
