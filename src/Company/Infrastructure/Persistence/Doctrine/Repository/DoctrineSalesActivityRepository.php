<?php

namespace Company\Infrastructure\Persistence\Doctrine\Repository;

use Company\Domain\Model\SalesActivity;
use Company\Domain\Task\InCompany\SalesActivity\SalesActivityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrinePaginationListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;

class DoctrineSalesActivityRepository extends DoctrineEntityRepository implements SalesActivityRepository
{

    public function add(SalesActivity $salesActivity): void
    {
        $this->persist($salesActivity);
    }

    public function anInitialSalesActivity(): ?SalesActivity
    {
        return $this->findOneBy([
                    'disabled' => false,
                    'initial' => true,
        ]);
    }

    public function salesAcivityDetail(string $id): array
    {
        return $this->fetchOneByIdOrDie($id);
    }

    public function salesAcivityList(array $paginationSchema): array
    {
        $doctrinePaginationListCategory = DoctrinePaginationListCategory::fromSchema($paginationSchema);
        return $this->fetchPaginationList($doctrinePaginationListCategory);
    }

    public function fetchInitialSalesActivityDetail(): array
    {
        $filters = [
            new Filter(true, 'SalesActivity.initial'),
            new Filter(false, 'SalesActivity.disabled'),
        ];
        return $this->fetchOneBy($filters);
    }
}
