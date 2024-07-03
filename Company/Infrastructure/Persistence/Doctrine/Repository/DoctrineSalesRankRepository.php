<?php

namespace Company\Infrastructure\Persistence\Doctrine\Repository;

use Company\Domain\Model\SalesRank;
use Company\Domain\Task\SalesRank\SalesRankRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;

class DoctrineSalesRankRepository extends DoctrineEntityRepository implements SalesRankRepository
{

    public function allActive(): array
    {
        return $this->findBy([
                    'disabled' => false,
        ]);
    }

    public function add(SalesRank $salesRank): void
    {
        $this->persist($salesRank);
    }

    public function ofId(string $id): SalesRank
    {
        return $this->findOneByIdOrDie($id);
    }

    //
    public function aSalesRank(string $id)
    {
        return $this->queryOneById($id);
    }

    public function salesRankList(array $paginationSchema)
    {
        return $this->queryPaginationList($paginationSchema);
    }
}
