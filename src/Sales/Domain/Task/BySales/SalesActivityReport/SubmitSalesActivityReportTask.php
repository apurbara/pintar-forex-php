<?php

namespace Sales\Domain\Task\BySales\SalesActivityReport;

use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule\SalesActivityReportData;
use Sales\Domain\Task\BySales\SalesActivitySchedule\SalesActivityScheduleRepository;
use Sales\Domain\Task\BySales\SalesTask;

class SubmitSalesActivityReportTask implements SalesTask
{

    public function __construct(
            protected SalesActivityReportRepository $salesActivityReportRepository,
            protected SalesActivityScheduleRepository $salesActivityScheduleRepository)
    {
        
    }

    /**
     * 
     * @param Sales $sales
     * @param SalesActivityReportData $payload
     * @return void
     */
    public function executeBySales(Sales $sales, $payload): void
    {
        $payload->setId($this->salesActivityReportRepository->nextIdentity());

        $salesActivitySchedule = $this->salesActivityScheduleRepository->ofId($payload->salesActivityScheduleId);
        $salesActivitySchedule->assertBelongsToSales($sales);

        $salesActivityReport = $salesActivitySchedule->submitReport($payload);
        $this->salesActivityReportRepository->add($salesActivityReport);
    }
}
