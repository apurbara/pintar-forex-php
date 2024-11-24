<?php

namespace Sales\Domain\Task\FactFindingAssignment;

use Sales\Domain\DependencyModel\Customer\VerificationReportData;
use Tests\Sales\Domain\Task\SalesTaskTestBase;

class UpdateCustomerRatingTest extends SalesTaskTestBase
{
    protected $task;
    protected $payload, $rating = 4;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareFactFindingAssignmentDependency();
        //
        $this->task = new UpdateCustomerRating($this->factFindingAssignmentRepository);
        $this->payload = (new UpdateCustomerRatingPayload())
                ->setId($this->factFindingAssignmentId)
                ->setRating($this->rating);
    }
    
    //
    protected function execute()
    {
        $this->task->executeBySales($this->sales, $this->payload);
    }
    public function test_execute_submitVerificationReportOnFactFindingAssignment()
    {
        $this->factFindingAssignment->expects($this->once())
                ->method('updateCustomerRating')
                ->with($this->rating);
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
