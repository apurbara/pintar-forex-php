<?php

namespace Sales\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\DBAL\Query\QueryBuilder;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineAllListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrinePaginationListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivitySchedule;
use Sales\Domain\Task\SalesActivitySchedule\SalesActivityScheduleRepository;

class DoctrineSalesActivityScheduleRepository extends DoctrineEntityRepository
        implements SalesActivityScheduleRepository
{

    //
    protected function createCoreQueryBuilder(): QueryBuilder
    {
        $qb = parent::createCoreQueryBuilder();
        $qb->innerJoin('SalesActivitySchedule', 'AssignedCustomer', 'AssignedCustomer',
                'SalesActivitySchedule.AssignedCustomer_id = AssignedCustomer.id');
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
            new Filter($salesId, 'AssignedCustomer.Sales_id'),
            new Filter($id, 'SalesActivitySchedule.id'),
        ];
        return $this->fetchOneOrDie($filters);
    }

    public function scheduledSalesActivityBelongsToSalesList(string $salesId, array $paginationSchema): array
    {
        $doctrinePaginationListCategory = DoctrinePaginationListCategory::fromSchema($paginationSchema)
                ->addFilter(new Filter($salesId, 'AssignedCustomer.Sales_id'));
        return $this->fetchPaginationList($doctrinePaginationListCategory);
    }

    public function totalSalesActivityScheduleBelongsToSales(string $salesId, array $searchSchema): int
    {
        $qb = $this->dbalQueryBuilder();
        $qb->select('COUNT(SalesActivitySchedule.id)')
                ->from('SalesActivitySchedule')
                ->innerJoin('SalesActivitySchedule', "AssignedCustomer", "AssignedCustomer",
                        "SalesActivitySchedule.AssignedCustomer_id = AssignedCustomer.id")
                ->andWhere('AssignedCustomer.Sales_id = :salesId')
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
                ->innerJoin('SalesActivitySchedule', "AssignedCustomer", "AssignedCustomer",
                        "SalesActivitySchedule.AssignedCustomer_id = AssignedCustomer.id")
                ->andWhere('AssignedCustomer.Sales_id = :salesId')
                ->addGroupBy('SalesActivitySchedule.startTime')
                ->addGroupBy('SalesActivitySchedule.endTime')
                ->addGroupBy('SalesActivitySchedule.status')
                ->setParameter('salesId', $salesId);

        return DoctrineAllListCategory::fromSchema($searchSchema)
                        ->fetchResult($qb);
    }
}
