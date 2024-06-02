<?php

namespace Company\Infrastructure\Persistence\Doctrine\Repository;

use Company\Application\Service\Manager\ManagerRepository as ManagerRepository2;
use Company\Domain\Model\Manager;
use Company\Domain\Task\InCompany\Manager\ManagerRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;

class DoctrineManagerRepository extends DoctrineEntityRepository implements ManagerRepository, ManagerRepository2
{

    public function add(Manager $manager): void
    {
        $this->persist($manager);
    }

    public function ofId(string $id): Manager
    {
        return $this->findOneByIdOrDie($id);
    }

    public function isEmailAvailable(string $email): bool
    {
        $filters = [
            new Filter($email, 'Manager.email'),
        ];
        return empty($this->fetchOneBy($filters));
    }

    //
    public function viewManagerDetail(string $id): array
    {
        return $this->queryOneById($id);
    }

    public function viewManagerList(array $paginationSchema): array
    {
        return $this->queryPaginationList($paginationSchema);
    }
}
