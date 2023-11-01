<?php

namespace Sales\Domain\Task\VerificationReport;

use Sales\Domain\Model\AreaStructure\Area\Customer\VerificationReportData;
use Tests\src\Sales\Domain\Task\SalesTaskTestBase;

class SubmitVerificationReportTaskTest extends SalesTaskTestBase
{
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareAssignedCustomerDependency();
        $this->prepareCustomerVerificationDependency();
        //
        $this->task = new SubmitVerificationReportTask($this->assignedCustomerRepository, $this->customerVerificationRepository);
        $this->payload = (new VerificationReportData('note'))
                ->setAssignedCustomerId($this->assignedCustomerId)
                ->setCustomerVerificationId($this->customerVerificationId);
    }
    
    //
    protected function execute()
    {
        $this->task->executeBySales($this->sales, $this->payload);
    }
    public function test_execute_submitVerificationReportOnAssignedCustomer()
    {
        $this->assignedCustomer->expects($this->once())
                ->method('submitVerificationReport')
                ->with($this->customerVerification, $this->payload);
        $this->execute();
    }
    public function test_execute_assertAssignedCustomerManageableBySales()
    {
        $this->assignedCustomer->expects($this->once())
                ->method('assertBelongsToSales')
                ->with($this->sales);
        $this->execute();
    }
}
