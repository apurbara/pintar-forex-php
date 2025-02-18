<?php

namespace Sales\Infrastructure\Persistence\Doctrine\Repository;

use DateTimeImmutable;
use Doctrine\DBAL\Query\QueryBuilder;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineAllListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrinePaginationListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule;
use Sales\Domain\Task\SalesActivitySchedule\SalesActivityScheduleRepository;

class DoctrineSalesActivityScheduleRepository extends DoctrineEntityRepository
        implements SalesActivityScheduleRepository
{

    //
    protected function createCoreQueryBuilder(): QueryBuilder
    {
        $qb = parent::createCoreQueryBuilder();
        $qb->innerJoin('SalesActivitySchedule', 'CustomerAssignment', 'CustomerAssignment',
                'SalesActivitySchedule.CustomerAssignment_id = CustomerAssignment.id');
        return $qb;
    }

    public function add(SalesActivitySchedule $scheduledSalesActivity): void
    {
        $this->persist($scheduledSalesActivity);
    }

    public function ofId(string $id): SalesActivitySchedule
    {
        return $this->findOneByIdOrDie($id);
    }

    public function scheduledSalesActivityBelongsToSalesDetail(string $salesId, string $id): array
    {
        $filters = [
            new Filter($salesId, 'CustomerAssignment.Sales_id'),
            new Filter($id, 'SalesActivitySchedule.id'),
        ];
        return $this->fetchOneOrDie($filters);
    }

    public function scheduledSalesActivityBelongsToSalesList(string $salesId, array $paginationSchema): array
    {
        $qb = $this->createCoreQueryBuilder()
                ->innerJoin('SalesActivitySchedule', 'SalesActivity', 'SalesActivity',
                        'SalesActivitySchedule.SalesActivity_id = SalesActivity.id')
                ->addSelect('SalesActivity.name salesActivityName')
                ->innerJoin('CustomerAssignment', 'Customer', 'Customer',
                        'CustomerAssignment.Customer_id = Customer.id')
                ->addSelect('Customer.name customerName')
                ->addSelect('Customer.phone customerPhone');
        $doctrinePaginationListCategory = DoctrinePaginationListCategory::fromSchema($paginationSchema)
                ->addFilter(new Filter($salesId, 'CustomerAssignment.Sales_id'));
        return $doctrinePaginationListCategory->paginateResult($qb, $this->getTableName());
    }

    public function totalSalesActivityScheduleBelongsToSales(string $salesId, array $searchSchema): int
    {
        $qb = $this->dbalQueryBuilder();
        $qb->select('COUNT(SalesActivitySchedule.id)')
                ->from('SalesActivitySchedule')
                ->innerJoin('SalesActivitySchedule', "CustomerAssignment", "CustomerAssignment",
                        "SalesActivitySchedule.CustomerAssignment_id = CustomerAssignment.id")
                ->andWhere('CustomerAssignment.Sales_id = :salesId')
                ->setParameter('salesId', $salesId);

        foreach ($searchSchema['filters'] ?? [] as $filterSchema) {
            Filter::fromSchema($filterSchema)->applyToQuery($qb);
        }

        return $qb->executeQuery()->fetchOne();
    }

    public function salesActivityScheduleSummaryBelongsToSales(string $salesId, array $searchSchema): array
    {
        $qb = $this->dbalQueryBuilder();
        $qb->addSelect('COUNT(SalesActivitySchedule.startTime) total')
                ->addSelect('SalesActivitySchedule.startTime startTime')
                ->addSelect('SalesActivitySchedule.endTime endTime')
                ->addSelect('SalesActivitySchedule.status status')
                ->from('SalesActivitySchedule')
                ->innerJoin('SalesActivitySchedule', "CustomerAssignment", "CustomerAssignment",
                        "SalesActivitySchedule.CustomerAssignment_id = CustomerAssignment.id")
                ->andWhere('CustomerAssignment.Sales_id = :salesId')
                ->addGroupBy('SalesActivitySchedule.startTime')
                ->addGroupBy('SalesActivitySchedule.endTime')
                ->addGroupBy('SalesActivitySchedule.status')
                ->setParameter('salesId', $salesId);

        return DoctrineAllListCategory::fromSchema($searchSchema)
                        ->fetchResult($qb);
    }

    public function queryAllList(array $searchSchema): array
    {
        $qb = $this->createCoreQueryBuilder()
                ->addOrderBy('SalesActivitySchedule.startTime', 'DESC');
        return DoctrineAllListCategory::fromSchema($searchSchema)
                        ->fetchResult($qb);
    }

    public function allNonInitialSchedulesInMonthBelongsToSales(string $salesId, int $year, int $month)
    {
        $monthFormat = (new DateTimeImmutable())->setDate($year, $month, 1)->format('Ym');
        $qb = $this->createCoreQueryBuilder();
        $qb->andWhere($qb->expr()->eq('CustomerAssignment.Sales_id', ':salesId'))
                ->innerJoin('SalesActivitySchedule', 'SalesActivity', 'SalesActivity',
                        'SalesActivitySchedule.SalesActivity_id = SalesActivity.id')
                ->andWhere($qb->expr()->eq('SalesActivity.initial', 0))
                ->setParameter('salesId', $salesId)
                ->andWhere($qb->expr()->eq("DATE_FORMAT(SalesActivitySchedule.startTime, '%Y%m')", "'$monthFormat'"));
        return $qb->executeQuery()->fetchAllAssociative();
    }

    public function allNonInitialSchedulesBelongsToSales(string $salesId)
    {
        $qb = $this->createCoreQueryBuilder();
        $qb->andWhere($qb->expr()->eq('CustomerAssignment.Sales_id', ':salesId'))
                ->innerJoin('SalesActivitySchedule', 'SalesActivity', 'SalesActivity',
                        'SalesActivitySchedule.SalesActivity_id = SalesActivity.id')
                ->andWhere($qb->expr()->eq('SalesActivity.initial', 0))
                ->setParameter('salesId', $salesId);
        return $qb->executeQuery()->fetchAllAssociative();
    }
}
