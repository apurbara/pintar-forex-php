<?php

namespace Manager\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\DBAL\Query\QueryBuilder;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer\RecycleRequest;
use Manager\Domain\Task\RecycleRequest\RecycleRequestRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineAllListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrinePaginationListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;
use Respect\Validation\Rules\DateTime;
use SharedContext\Domain\Enum\ManagementApprovalStatus;

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

    public function monthlyRecycledCount(string $managerId, array $searchSchema): array
    {
        $qb = $this->dbalQueryBuilder();
        $qb->select('COUNT(*) recycledCount')
                ->addSelect('EXTRACT(YEAR_MONTH FROM RecycleRequest.createdTime) yearMonth')
                ->from('RecycleRequest')
                ->innerJoin('RecycleRequest', 'AssignedCustomer', 'AssignedCustomer',
                        'RecycleRequest.AssignedCustomer_id = AssignedCustomer.id')
                ->innerJoin('AssignedCustomer', 'Sales', 'Sales', 'AssignedCustomer.Sales_id = Sales.id')
                ->andWhere($qb->expr()->eq('RecycleRequest.status', ManagementApprovalStatus::APPROVED->value))
                ->andWhere($qb->expr()->eq('Sales.Manager_id', ':managerId'))
                ->setParameter('managerId', $managerId)
                ->groupBy('yearMonth');
        
        $startMonthDefined = false;
        $endMonthDefined = false;
        foreach ($searchSchema['filters'] ?? [] as $filter) {
            if ($filter['columns'] ?? null === 'yearMonth' && $filter['comparisonType'] ?? null === 'GTE') {
                $startMonthDefined = true;
                $qb->andWhere($qb->expr()->gte('EXTRACT(YEAR_MONTH FROM RecycleRequest.createdTime)',
                                $filter['value'] ?? (new DateTime('-12 months'))->format('Ym')));
                break;
            }
            if ($filter['columns'] ?? null === 'yearMonth' && $filter['comparisonType'] ?? null === 'LTE') {
                $endMonthDefined = true;
                $qb->andWhere($qb->expr()->lte('EXTRACT(YEAR_MONTH FROM RecycleRequest.createdTime)', $filter['value'] ?? (new DateTime())->format('Ym')));
                break;
            }
        }
        
        if (!$startModifiedTimeDefined) {
            $qb->andWhere($qb->expr()->gte('EXTRACT(YEAR_MONTH FROM RecycleRequest.createdTime', (new DateTime('-12 months'))->format('Ym')));
        }
        if (!$endModifiedTimeDefined) {
            $qb->andWhere($qb->expr()->lte('EXTRACT(YEAR_MONTH FROM RecycleRequest.createdTime', (new DateTime())->format('Ym')));
        }
        return DoctrineAllListCategory::fromSchema()
                ->fetchResult($qb);
    }
}
