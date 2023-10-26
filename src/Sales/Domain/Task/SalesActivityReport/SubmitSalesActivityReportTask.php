<?php

namespace Sales\Domain\Task\SalesActivityReport;

use Sales\Domain\Model\Personnel\Sales;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivitySchedule\SalesActivityReportData;
use Sales\Domain\Task\SalesActivitySchedule\SalesActivityScheduleRepository;
use Sales\Domain\Task\SalesTask;

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
