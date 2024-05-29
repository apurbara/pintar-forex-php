<?php

namespace Sales\Domain\Task\BySales\VerificationReport;

use Sales\Domain\DependencyModel\AreaStructure\Area\Customer\VerificationReportData;
use Tests\src\Sales\Domain\Task\BySales\SalesTaskTestBase;

class SubmitVerificationReportTaskTest extends SalesTaskTestBase
{
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareCustomerAssignmentDependency();
        $this->prepareCustomerVerificationDependency();
        //
        $this->task = new SubmitVerificationReportTask($this->customerAssignmentRepository, $this->customerVerificationRepository);
        $this->payload = (new VerificationReportData('note'))
                ->setCustomerAssignmentId($this->customerAssignmentId)
                ->setCustomerVerificationId($this->customerVerificationId);
    }
    
    //
    protected function execute()
    {
        $this->task->executeBySales($this->sales, $this->payload);
    }
    public function test_execute_submitVerificationReportOnCustomerAssignment()
    {
        $this->customerAssignment->expects($this->once())
                ->method('submitCustomerVerificationReport')
                ->with($this->customerVerification, $this->payload);
        $this->execute();
    }
    public function test_execute_assertCustomerAssignmentManageableBySales()
    {
        $this->customerAssignment->expects($this->once())
                ->method('assertBelongsToSales')
                ->with($this->sales);
        $this->execute();
    }
}
