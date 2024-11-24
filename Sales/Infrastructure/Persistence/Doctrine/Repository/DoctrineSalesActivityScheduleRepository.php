<?php

namespace Sales\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\DBAL\Query\QueryBuilder;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineAllListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrinePaginationListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule;
use Sales\Domain\Task\SalesActivitySchedule\SalesActivityScheduleRepository;
use Shared\Domain\Enum\SalesActivityScheduleStatus;

class DoctrineSalesActivityScheduleRepository extends DoctrineEntityRepository
        implements SalesActivityScheduleRepository
{

    public function add(SalesActivitySchedule $scheduledSalesActivity): void
    {
        $this->persist($scheduledSalesActivity);
    }

    public function ofId(string $id): SalesActivitySchedule
    {
        return $this->findOneByIdOrDie($id);
    }

    //
    public function queryAllList(array $searchSchema): array
    {
        $qb = $this->createCoreQueryBuilder()
                ->addOrderBy('SalesActivitySchedule.startTime', 'DESC');
        return DoctrineAllListCategory::fromSchema($searchSchema)
                        ->fetchResult($qb);
    }

    private function registerJoinToAllAssignmentType(QueryBuilder $qb): void
    {
        $qb->innerJoin('SalesActivitySchedule', 'CustomerAssignment', 'CustomerAssignment',
                        'SalesActivitySchedule.CustomerAssignment_id = CustomerAssignment.id')
                ->leftJoin('CustomerAssignment', 'GreetingAssignment', 'GreetingAssignment',
                        'GreetingAssignment.CustomerAssignment_id = CustomerAssignment.id')
                ->leftJoin('CustomerAssignment', 'FactFindingAssignment', 'FactFindingAssignment',
                        'FactFindingAssignment.CustomerAssignment_id = CustomerAssignment.id')
                ->leftJoin('CustomerAssignment', 'StrikingAssignment', 'StrikingAssignment',
                        'StrikingAssignment.CustomerAssignment_id = CustomerAssignment.id');
    }

    protected function createCoreQueryBuilder(): QueryBuilder
    {
        $qb = parent::createCoreQueryBuilder();
        $qb->addSelect('GreetingAssignment.id GreetingAssignment_id')
                ->addSelect('FactFindingAssignment.id FactFindingAssignment_id')
                ->addSelect('StrikingAssignment.id StrikingAssignment_id');
        $this->registerJoinToAllAssignmentType($qb);
        return $qb;
    }

    private function limitResultToSalesOwnershipOnly(QueryBuilder $qb, string $salesId): void
    {
        $qb->andWhere($qb->expr()->or(
                                $qb->expr()->eq('GreetingAssignment.Sales_id', ':salesId'),
                                $qb->expr()->eq('FactFindingAssignment.Sales_id', ':salesId'),
                                $qb->expr()->eq('StrikingAssignment.Sales_id', ':salesId')
                        ))
                ->setParameter('salesId', $salesId);
    }

    public function aSalesActivityScheduleBelongsToSales(string $salesId, string $id): array
    {
        $qb = $this->createCoreQueryBuilder();
        $this->limitResultToSalesOwnershipOnly($qb, $salesId);
        $qb->andWhere($qb->expr()->eq('SalesActivitySchedule.id', ':id'))
                ->setParameter('id', $id)
                ->setMaxResults(1);
        $this->limitResultToSalesOwnershipOnly($qb, $salesId);
        return $qb->executeQuery()->fetchAssociative() ?: null;
    }

    public function salesActivityScheduleListBelongsToSales(string $salesId, array $paginationSchema): array
    {
        $qb = $this->createCoreQueryBuilder();
        $this->limitResultToSalesOwnershipOnly($qb, $salesId);
        return DoctrinePaginationListCategory::fromSchema($paginationSchema)
                        ->paginateResult($qb, $this->getTableName());
    }

    public function totalSalesActivityScheduleBelongsToSales(string $salesId, array $searchSchema): int
    {
        $qb = $this->dbalQueryBuilder();
        $qb->select('COUNT(SalesActivitySchedule.id)')
                ->from('SalesActivitySchedule');
        $this->registerJoinToAllAssignmentType($qb);
        $this->limitResultToSalesOwnershipOnly($qb, $salesId);

        foreach ($searchSchema['filters'] ?? [] as $filterSchema) {
            Filter::fromSchema($filterSchema)->applyToQuery($qb);
        }

        return $qb->executeQuery()->fetchOne();
    }

    public function allOngoingSalesActivityScheduleBelongsToSales(string $salesId, array $searchSchema): array
    {
        $qb = $this->createCoreQueryBuilder();
        $this->limitResultToSalesOwnershipOnly($qb, $salesId);
        $qb->andWhere($qb->expr()->eq('SalesActivitySchedule.status', "'" . SalesActivityScheduleStatus::SCHEDULED->value . "'"));
        
        return DoctrineAllListCategory::fromSchema($searchSchema)
                ->fetchResult($qb);
    }
}
