<?php

namespace Sales\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\DBAL\Query\QueryBuilder;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrinePaginationListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivitySchedule\SalesActivityReport;
use Sales\Domain\Task\SalesActivityReport\SalesActivityReportRepository;

class DoctrineSalesActivityReportRepository extends DoctrineEntityRepository implements SalesActivityReportRepository
{

    protected function createCoreQueryBuilder(): QueryBuilder
    {
        return parent::createCoreQueryBuilder()
                        ->innerJoin('SalesActivityReport', 'SalesActivitySchedule', 'SalesActivitySchedule',
                                'SalesActivityReport.SalesActivitySchedule_id = SalesActivitySchedule.id')
                        ->innerJoin('SalesActivitySchedule', 'AssignedCustomer', 'AssignedCustomer',
                                'SalesActivitySchedule.AssignedCustomer_id = AssignedCustomer.id');
    }

    //
    public function add(SalesActivityReport $salesActivityReport): void
    {
        $this->persist($salesActivityReport);
    }

    public function salesActivityReportDetailBelongsToSales(string $salesId, string $id): array
    {
        $filters = [
            new Filter($salesId, 'AssignedCustomer.Sales_id'),
            new Filter($id, 'SalesActivityReport.id'),
        ];
        return $this->fetchOneOrDie($filters);
    }

    public function aSalesActivityReportAssociateWithSchedule(string $scheduleId)
    {
        $filters = [
            new Filter($scheduleId, 'SalesActivityReport.SalesActivitySchedule_id'),
        ];
        return $this->fetchOneBy($filters);
    }

    public function salesActivityReportListBelongsToSales(string $salesId, array $paginationSchema): array
    {
        $doctrinePaginationListCategory = DoctrinePaginationListCategory::fromSchema($paginationSchema)
                ->addFilter(new Filter($salesId, 'AssignedCustomer.Sales_id'));
        return $this->fetchPaginationList($doctrinePaginationListCategory);
    }

}
