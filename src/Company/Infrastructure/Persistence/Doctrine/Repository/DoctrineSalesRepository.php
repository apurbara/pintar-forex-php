<?php

namespace Company\Infrastructure\Persistence\Doctrine\Repository;

use Company\Application\Service\Sales\SalesRepository as SalesRepository2;
use Company\Domain\Model\Sales;
use Company\Domain\Task\InCompany\Sales\SalesRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;

class DoctrineSalesRepository extends DoctrineEntityRepository implements SalesRepository, SalesRepository2
{

    public function add(Sales $sales): void
    {
        $this->persist($sales);
    }

    public function ofId(string $id): Sales
    {
        return $this->findOneByIdOrDie($id);
    }

    public function isEmailAvailable(string $email): bool
    {
        $filters = [
            new Filter($email, 'Sales.email'),
            new Filter(false, 'Sales.cancelled'),
        ];
        return empty($this->fetchOneBy($filters));
    }

    //

    public function aSales(string $id)
    {
        return $this->queryOneById($id);
    }

    public function salesList(array $paginationSchema): array
    {
        return $this->queryPaginationList($paginationSchema);
    }

    public function allSales(array $searchSchema)
    {
        return $this->queryAllList($searchSchema);
    }
}
