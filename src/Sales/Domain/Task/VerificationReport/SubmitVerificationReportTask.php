<?php

namespace Sales\Domain\Task\VerificationReport;

use Sales\Domain\Model\AreaStructure\Area\Customer\VerificationReportData;
use Sales\Domain\Model\Personnel\Sales;
use Sales\Domain\Task\AssignedCustomer\AssignedCustomerRepository;
use Sales\Domain\Task\CustomerVerification\CustomerVerificationRepository;
use Sales\Domain\Task\SalesTask;

class SubmitVerificationReportTask implements SalesTask
{

    public function __construct(
            protected AssignedCustomerRepository $assignedCustomerRepository,
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
        $assignedCustomer = $this->assignedCustomerRepository->ofId($payload->assignedCustomerId);
        $assignedCustomer->assertBelongsToSales($sales);
        
        $assignedCustomer->submitVerificationReport($customerVerification, $payload);
    }
}
