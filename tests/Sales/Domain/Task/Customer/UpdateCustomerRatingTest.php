<?php

namespace Sales\Domain\Task\Customer;

use Sales\Domain\Model\Sales\ContainCustomerAssignmentInterface;
use Sales\Domain\Task\Dependency\ContainCustomerAssignmentRepository;
use Tests\Sales\Domain\Task\SalesTaskTestBase;

class UpdateCustomerRatingTest extends SalesTaskTestBase
{
    protected $customerAssignmentRepository, $customerAssignment, $customerAssignmentId = 'customerAssignmentId';
    protected $task;
    protected $payload, $rating = 4;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->customerAssignmentRepository= $this->buildMockOfInterface(ContainCustomerAssignmentRepository::class);
        $this->customerAssignment = $this->buildMockOfInterface(ContainCustomerAssignmentInterface::class);
        //
        $this->task = new UpdateCustomerRating($this->customerAssignmentRepository);
        $this->payload = (new UpdateCustomerRatingPayload())
                ->setId($this->customerAssignmentId)
                ->setRating($this->rating);
    }
    
    //
    protected function execute()
    {
        $this->customerAssignmentRepository->expects($this->any())
                ->method('ofId')
                ->with($this->customerAssignmentId)
                ->willReturn($this->customerAssignment);
        $this->task->executeBySales($this->sales, $this->payload);
    }
    public function test_execute_submitVerificationReportOnFactFindingAssignment()
    {
        $this->customerAssignment->expects($this->once())
                ->method('updateCustomerRating')
                ->with($this->rating);
        $this->execute();
    }
    public function test_execute_assertFactFindingAssignmentManageableBySales()
    {
        $this->customerAssignment->expects($this->once())
                ->method('assertBelongsToSales')
                ->with($this->sales);
        $this->execute();
    }
}
