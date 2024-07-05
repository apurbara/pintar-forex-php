<?php

namespace Sales\Domain\Task\VerificationReport;

use Sales\Domain\DependencyModel\Customer\VerificationReportData;
use Tests\Sales\Domain\Task\SalesTaskTestBase;

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
        $this->payload = (new VerificationReportData())
                ->setNote('note')
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
