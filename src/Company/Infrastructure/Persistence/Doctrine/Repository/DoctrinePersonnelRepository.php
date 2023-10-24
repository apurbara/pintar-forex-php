<?php

namespace Company\Infrastructure\Persistence\Doctrine\Repository;

use Company\Domain\Model\Personnel;
use Company\Domain\Task\InCompany\Personnel\PersonnelRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrinePaginationListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;

class DoctrinePersonnelRepository extends DoctrineEntityRepository implements PersonnelRepository
{
    
    public function add(Personnel $personnel): void
    {
        $this->persist($personnel);
    }

    public function viewPersonnelDetail(string $id): array
    {
        return $this->fetchOneByIdOrDie($id);
    }

    public function viewPersonnelList(array $paginationSchema): array
    {
        $doctrinePaginationListCategory = DoctrinePaginationListCategory::fromSchema($paginationSchema);
        return $this->fetchPaginationList($doctrinePaginationListCategory);
    }

    public function isEmailAvailable(string $email): bool
    {
        $filters = [
            new Filter($email, 'Personnel.email'),
        ];
        return empty($this->fetchOneBy($filters));
    }

    public function ofId(string $id): Personnel
    {
        return $this->findOneByIdOrDie($id);
    }
}
