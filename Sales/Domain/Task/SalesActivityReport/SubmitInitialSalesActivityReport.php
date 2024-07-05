<?php

namespace Sales\Domain\Task\SalesActivityReport;

use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule\SalesActivityReport;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivityScheduleData;
use Sales\Domain\Task\CustomerAssignment\CustomerAssignmentRepository;
use Sales\Domain\Task\SalesActivitySchedule\SalesActivityScheduleRepository;
use Sales\Domain\Task\SalesTask;
use Sales\Domain\Task\Dependency\SalesActivityRepository;
use SharedContext\Domain\ValueObject\HourlyTimeIntervalData;

class SubmitInitialSalesActivityReport implements SalesTask
{

    public function __construct(protected SalesActivityReportRepository $repository,
            protected CustomerAssignmentRepository $customerAssignmentRepository,
            protected SalesActivityScheduleRepository $salesActivityScheduleRepository,
            protected SalesActivityRepository $salesActivityRepository)
    {
        
    }

    /**
     * 
     * @param Sales $sales
     * @param SubmitInitialSalesActivityReportPayload $payload
     * @return void
     */
    public function executeBySales(Sales $sales, $payload): void
    {
        $payload->setId($this->repository->nextIdentity());
        $payload->setSalesActivityScheduleId($this->salesActivityScheduleRepository->nextIdentity());
        //
        $customerAssignment = $this->customerAssignmentRepository->ofId($payload->customerAssignmentId);
        $customerAssignment->assertBelongsToSales($sales);
        
        $salesActivity = $this->salesActivityRepository->anInitialSalesActivity();
        
        $data = (new SalesActivityScheduleData(new HourlyTimeIntervalData('now')))
                ->setId($payload->salesActivityScheduleId);
        
        $salesActivitySchedule = new SalesActivitySchedule($customerAssignment, $salesActivity, $data);
        $salesActivityReport = new SalesActivityReport($salesActivitySchedule, $payload);
        $this->repository->add($salesActivityReport);
    }
}
