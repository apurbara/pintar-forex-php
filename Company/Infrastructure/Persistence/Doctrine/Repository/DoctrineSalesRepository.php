<?php

namespace Company\Infrastructure\Persistence\Doctrine\Repository;

use Company\Domain\Model\Manager\Sales;
use Company\Domain\Task\Sales\SalesRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;

class DoctrineSalesRepository extends DoctrineEntityRepository implements SalesRepository
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
            new Filter(false, 'Sales.contractTerminated'),
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
