<?php

namespace Sales\Domain\Task\BySales\VerificationReport;

use Sales\Domain\DependencyModel\AreaStructure\Area\Customer\VerificationReportData;
use Sales\Domain\Model\Sales;
use Sales\Domain\Task\BySales\CustomerAssignment\CustomerAssignmentRepository;
use Sales\Domain\Task\BySales\SalesTask;
use Sales\Domain\Task\Dependency\CustomerVerificationRepository;

class SubmitVerificationReportTask implements SalesTask
{

    public function __construct(
            protected CustomerAssignmentRepository $customerAssignmentRepository,
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
        $customerVerification = $this->customerVerificationRepository->ofId($payload->customerVerificationId);
        $customerAssignment = $this->customerAssignmentRepository->ofId($payload->customerAssignmentId);
        $customerAssignment->assertBelongsToSales($sales);

        $customerAssignment->SubmitCustomerVerificationReport($customerVerification, $payload);
    }
}
