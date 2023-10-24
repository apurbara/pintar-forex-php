<?php

namespace Company\Infrastructure\Persistence\Doctrine\Repository;

use Company\Domain\Model\Personnel\Manager\Sales;
use Company\Domain\Task\InCompany\Personnel\Manager\Sales\SalesRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrinePaginationListCategory;

class DoctrineSalesRepository extends DoctrineEntityRepository implements SalesRepository
{

    public function add(Sales $sales): void
    {
        $this->persist($sales);
    }

    public function salesDetail(string $id): array
    {
        return $this->fetchOneByIdOrDie($id);
    }

    public function salesList(array $paginationSchema): array
    {
        $doctrinePaginationListCategory = DoctrinePaginationListCategory::fromSchema($paginationSchema);
        return $this->fetchPaginationList($doctrinePaginationListCategory);
    }
}
