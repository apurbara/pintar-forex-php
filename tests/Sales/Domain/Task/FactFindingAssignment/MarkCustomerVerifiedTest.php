<?php

namespace Sales\Domain\Task\FactFindingAssignment;

use Tests\Sales\Domain\Task\SalesTaskTestBase;

class MarkCustomerVerifiedTest extends SalesTaskTestBase
{
    protected $task;
    //
    protected $allActiveCustomerVerification;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareFactFindingAssignmentDependency();
        $this->prepareCustomerVerificationDependency();
        //
        $this->task = new MarkCustomerVerified($this->factFindingAssignmentRepository, $this->customerVerificationRepository, $this->dispatcher);
        
        $this->allActiveCustomerVerification = [$this->customerVerification];
    }
    
    //
    protected function execute()
    {
        $this->customerVerificationRepository->expects($this->any())
                ->method('allActiveCustomerVerification')
                ->willReturn($this->allActiveCustomerVerification);
        $this->task->executeBySales($this->sales, $this->factFindingAssignmentId);
    }
    public function test_execute_submitVerificationReportOnFactFindingAssignment()
    {
        $this->factFindingAssignment->expects($this->once())
                ->method('markCustomerVerified')
                ->with($this->allActiveCustomerVerification);
        $this->execute();
    }
    public function test_execute_assertFactFindingAssignmentManageableBySales()
    {
        $this->factFindingAssignment->expects($this->once())
                ->method('assertBelongsToSales')
                ->with($this->sales);
        $this->execute();
    }
    public function test_execute_publishDispatcher_dispatchFactFindingAssignmentEventContainer()
    {
        $this->dispatcher->expects($this->once())
                ->method('dispatchEventContainer')
                ->with($this->factFindingAssignment);
        $this->execute();
    }
}
