<?php

namespace Sales\Domain\Task\FactFindingAssignment;

use Sales\Domain\DependencyModel\Customer\VerificationReportData;
use Tests\Sales\Domain\Task\SalesTaskTestBase;

class SubmitVerificationReportTest extends SalesTaskTestBase
{
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareFactFindingAssignmentDependency();
        $this->prepareCustomerVerificationDependency();
        //
        $this->task = new SubmitVerificationReport($this->factFindingAssignmentRepository, $this->customerVerificationRepository);
        $this->payload = (new VerificationReportData())
                ->setNote('note')
                ->setFactFindingAssignmentId($this->factFindingAssignmentId)
                ->setCustomerVerificationId($this->customerVerificationId);
    }
    
    //
    protected function execute()
    {
        $this->task->executeBySales($this->sales, $this->payload);
    }
    public function test_execute_submitVerificationReportOnFactFindingAssignment()
    {
        $this->factFindingAssignment->expects($this->once())
                ->method('submitCustomerVerificationReport')
                ->with($this->customerVerification, $this->payload);
        $this->execute();
    }
    public function test_execute_assertFactFindingAssignmentManageableBySales()
    {
        $this->factFindingAssignment->expects($this->once())
                ->method('assertBelongsToSales')
                ->with($this->sales);
        $this->execute();
    }
}
