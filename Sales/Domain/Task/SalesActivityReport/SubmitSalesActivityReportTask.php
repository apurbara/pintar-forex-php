<?php

namespace Sales\Domain\Task\SalesActivityReport;

use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule\SalesActivityReport;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule\SalesActivityReportData;
use Sales\Domain\Task\SalesActivitySchedule\SalesActivityScheduleRepository;
use Sales\Domain\Task\SalesTask;

class SubmitSalesActivityReportTask implements SalesTask
{

    public function __construct(
            protected SalesActivityReportRepository $repository,
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
        $payload->setId($this->repository->nextIdentity());

        $salesActivitySchedule = $this->salesActivityScheduleRepository->ofId($payload->salesActivityScheduleId);
        $salesActivitySchedule->assertBelongsToSales($sales);
        
        $salesActivityReport = new SalesActivityReport($salesActivitySchedule, $payload->id, $payload);
        $this->repository->add($salesActivityReport);
    }
}
