<?php

namespace Manager\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\DBAL\Query\QueryBuilder;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer\RecycleRequest;
use Manager\Domain\Task\RecycleRequest\RecycleRequestRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrinePaginationListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;

class DoctrineRecycleRequestRepository extends DoctrineEntityRepository implements RecycleRequestRepository
{

    protected function createCoreQueryBuilder(): QueryBuilder
    {
        return parent::createCoreQueryBuilder()
                        ->innerJoin('RecycleRequest', 'AssignedCustomer', 'AssignedCustomer',
                                'RecycleRequest.AssignedCustomer_id = AssignedCustomer.id')
                        ->innerJoin('AssignedCustomer', 'Sales', 'Sales', 'AssignedCustomer.Sales_id = Sales.id');
    }

    //
    public function aRecycleRequestBelongToManager(string $managerId, string $id): ?array
    {
        $filters = [
            new Filter($managerId, 'Sales.Manager_id'),
            new Filter($id, 'RecycleRequest.id'),
        ];
        return $this->fetchOneBy($filters);
    }

    public function recycleRequestListBelongToManager(string $managerId, array $paginationSchema): array
    {
        $doctrinePaginationListCategory = DoctrinePaginationListCategory::fromSchema($paginationSchema)
                ->addFilter(new Filter($managerId, 'Sales.Manager_id'));
        return $this->fetchPaginationList($doctrinePaginationListCategory);
    }

    public function ofId(string $id): RecycleRequest
    {
        return $this->findOneByIdOrDie($id);
    }
}
