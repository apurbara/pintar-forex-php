<?php

namespace Sales\Domain\Task\FactFindingAssignment;

use Sales\Domain\DependencyModel\Customer\VerificationReportData;
use Sales\Domain\Model\Sales;
use Sales\Domain\Task\Dependency\CustomerVerificationRepository;
use Sales\Domain\Task\SalesTask;

class SubmitVerificationReport implements SalesTask
{

    public function __construct(
            protected FactFindingAssignmentRepository $repository,
            protected CustomerVerificationRepository $customerVerificationRepository)
    {
        
    }

    /**
     * 
     * @param Sales $sales
     * @param VerificationReportData $payload
     * @return void
     */
    public function executeBySales(Sales $sales, $payload): void
    {
        $factFindingAssignment = $this->repository->ofId($payload->factFindingAssignmentId);
        $customerVerification = $this->customerVerificationRepository->ofId($payload->customerVerificationId);
        
        $factFindingAssignment->assertBelongsToSales($sales);
        $factFindingAssignment->SubmitCustomerVerificationReport($customerVerification, $payload);
    }
}
