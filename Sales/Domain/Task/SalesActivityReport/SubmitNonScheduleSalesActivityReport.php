<?php

namespace Sales\Domain\Task\SalesActivityReport;

use Sales\Domain\Model\Sales;
use Sales\Domain\Task\Dependency\ContainCustomerAssignmentRepository;
use Sales\Domain\Task\Dependency\SalesActivityRepository;
use Sales\Domain\Task\SalesTask;

class SubmitNonScheduleSalesActivityReport implements SalesTask
{

    public function __construct(
            protected SalesActivityReportRepository $repository,
            protected ContainCustomerAssignmentRepository $customerAssignmentRepository,
            protected SalesActivityRepository $salesActivityRepository)
    {
        
    }

    /**
     * 
     * @param Sales $sales
     * @param SubmitNonScheduleSalesActivityReportPayload $payload
     * @return void
     */
    public function executeBySales(Sales $sales, $payload): void
    {
        $payload->setId($this->repository->nextIdentity());
        
        $customerAssignment = $this->customerAssignmentRepository->ofId($payload->customerAssignmentId);
        $salesActivity = $this->salesActivityRepository->ofId($payload->salesActivityId);
        
        $customerAssignment->assertBelongsToSales($sales);
        $salesActivityReport = $customerAssignment->submitNonScheduledSalesActivityReport($salesActivity, $payload->id, $payload);
        $this->repository->add($salesActivityReport);
    }
}
