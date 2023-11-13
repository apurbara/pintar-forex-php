<?php

namespace Manager\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\DBAL\Query\QueryBuilder;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer\ClosingRequest;
use Manager\Domain\Task\ClosingRequest\ClosingRequestRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrinePaginationListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;

class DoctrineClosingRequestRepository extends DoctrineEntityRepository implements ClosingRequestRepository
{

    protected function createCoreQueryBuilder(): QueryBuilder
    {
        return parent::createCoreQueryBuilder()
                        ->innerJoin('ClosingRequest', 'AssignedCustomer', 'AssignedCustomer',
                                'ClosingRequest.AssignedCustomer_id = AssignedCustomer.id')
                        ->innerJoin('AssignedCustomer', 'Sales', 'Sales', 'AssignedCustomer.Sales_id = Sales.id');
    }

    //
    public function aClosingRequestBelongToManager(string $managerId, string $id): ?array
    {
        $filters = [
            new Filter($managerId, 'Sales.Manager_id'),
            new Filter($id, 'ClosingRequest.id'),
        ];
        return $this->fetchOneBy($filters);
    }

    public function closingRequestListBelongToManager(string $managerId, array $paginationSchema): array
    {
        $doctrinePaginationListCategory = DoctrinePaginationListCategory::fromSchema($paginationSchema)
                ->addFilter(new Filter($managerId, 'Sales.Manager_id'));
        return $this->fetchPaginationList($doctrinePaginationListCategory);
    }

    public function ofId(string $id): ClosingRequest
    {
        return $this->findOneByIdOrDie($id);
    }
}
