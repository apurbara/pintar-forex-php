<?php

namespace Manager\Infrastructure\Persistence\Doctrine\Repository;

use DateTime;
use Doctrine\DBAL\Query\QueryBuilder;
use Manager\Domain\Model\Manager\Sales\CustomerAssignment\RecycleRequest;
use Manager\Domain\Task\RecycleRequest\RecycleRequestRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineAllListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrinePaginationListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;
use SharedContext\Domain\Enum\ManagementApprovalStatus;

class DoctrineRecycleRequestRepository extends DoctrineEntityRepository implements RecycleRequestRepository
{

    public function ofId(string $id): RecycleRequest
    {
        return $this->findOneByIdOrDie($id);
    }

    //
    private function createAggregateCoreQueryBuilder(string $managerId): QueryBuilder
    {
        $qb = $this->createCoreQueryBuilder();
        return $qb->innerJoin('RecycleRequest', 'CustomerAssignment', 'CustomerAssignment',
                                'RecycleRequest.CustomerAssignment_id = CustomerAssignment.id')
                        ->innerJoin('CustomerAssignment', 'Sales', 'Sales', 'CustomerAssignment.Sales_id = Sales.id')
                        ->andWhere($qb->expr()->eq('Sales.Manager_id', ':managerId'))
                        ->setParameter('managerId', $managerId);
    }

    public function aRecycleRequestBelongsToManager(string $managerId, string $id): ?array
    {
        return $this->retrieveOne($this->createAggregateCoreQueryBuilder($managerId), [new Filter($id, 'RecycleRequest.id')]);
    }

    public function monthlyRecycledCountBelongsToManager(string $managerId, array $searchSchema): int
    {
        $qb = $this->dbalQueryBuilder();
        $qb->select('COUNT(*) recycledCount')
                ->addSelect('EXTRACT(YEAR_MONTH FROM RecycleRequest.createdTime) yearMonth')
                ->from('RecycleRequest')
                ->innerJoin('RecycleRequest', 'CustomerAssignment', 'CustomerAssignment',
                        'RecycleRequest.CustomerAssignment_id = CustomerAssignment.id')
                ->innerJoin('CustomerAssignment', 'Sales', 'Sales', 'CustomerAssignment.Sales_id = Sales.id')
                ->andWhere($qb->expr()->eq('Sales.Manager_id', ':managerId'))
                ->setParameter('managerId', $managerId)
                ->andWhere($qb->expr()->eq('RecycleRequest.status', ManagementApprovalStatus::APPROVED->value))
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
                $qb->andWhere($qb->expr()->lte('EXTRACT(YEAR_MONTH FROM RecycleRequest.createdTime)',
                                $filter['value'] ?? (new DateTime())->format('Ym')));
                break;
            }
        }

        return DoctrineAllListCategory::fromSchema()
                        ->fetchResult($qb);
    }

    public function recycleRequestCountBelongsToManager(string $managerId, array $searchSchema): int
    {
        $qb = $this->dbalQueryBuilder();
        $qb->select('COUNT(*)')
                ->from('RecycleRequest')
                ->innerJoin('RecycleRequest', 'CustomerAssignment', 'CustomerAssignment',
                        'RecycleRequest.CustomerAssignment_id = CustomerAssignment.id')
                ->innerJoin('CustomerAssignment', 'Sales', 'Sales', 'CustomerAssignment.Sales_id = Sales.id')
                ->andWhere($qb->expr()->eq('Sales.Manager_id', ':managerId'))
                ->setParameter('managerId', $managerId);
        foreach ($searchSchema['filters'] ?? [] as $filterSchema) {
            Filter::fromSchema($filterSchema)->applyToQuery($qb);
        }
        return $qb->executeQuery()->fetchOne();
    }

    public function recycleRequestListBelongsToManager(string $managerId, array $paginationSchema): array
    {
        return DoctrinePaginationListCategory::fromSchema($paginationSchema)
                        ->paginateResult($this->createAggregateCoreQueryBuilder($managerId), $this->getTableName());
    }
}
