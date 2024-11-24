<?php

namespace Sales\Domain\Task\SalesActivityReport;

use Sales\Domain\Model\Sales\ContainCustomerAssignmentInterface;
use Sales\Domain\Task\Dependency\ContainCustomerAssignmentRepository;
use Tests\Sales\Domain\Task\SalesTaskTestBase;

class SubmitNonScheduleSalesActivityReportTest extends SalesTaskTestBase
{
    protected $customerAssignmentRepository, $customerAssignment, $customerAssignmentId = 'customerAssignmentId';
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareSalesActivityReportDependency();
        $this->prepareSalesActivityDependency();
        $this->customerAssignmentRepository = $this->buildMockOfInterface(ContainCustomerAssignmentRepository::class);
        $this->customerAssignment = $this->buildMockOfInterface(ContainCustomerAssignmentInterface::class);
        $this->customerAssignmentRepository->expects($this->any())
                ->method('ofId')
                ->with($this->customerAssignmentId)
                ->willReturn($this->customerAssignment);
        
        $this->task = new SubmitNonScheduleSalesActivityReport($this->salesActivityReportRepository, $this->customerAssignmentRepository, $this->salesActivityRepository);
        
        $this->payload = (new SubmitNonScheduleSalesActivityReportPayload('content'))
                ->setCustomerAssignmentId($this->customerAssignmentId)
                ->setSalesActivityId($this->salesActivityId);
    }
    
    protected function execute()
    {
        $this->salesActivityReportRepository->expects($this->any())
                ->method('nextIdentity')
                ->willReturn($this->salesActivityReportId);
        $this->task->executeBySales($this->sales, $this->payload);
    }
    public function test_execute_setPayloadId()
    {
        $this->execute();
        $this->assertSame($this->salesActivityReportId, $this->payload->id);
    }
    public function test_execute_addSalesActivityReportToRepository()
    {
        $this->customerAssignment->expects($this->once())
                ->method('submitNonScheduledSalesActivityReport')
                ->with($this->salesActivity, $this->salesActivityReportId, $this->payload)
                ->willReturn($this->salesActivityReport);
        $this->salesActivityReportRepository->expects($this->once())
                ->method('add')
                ->with($this->salesActivityReport);
        $this->execute();
    }
    public function test_execute_assertCustomerAssignmentBelongsToSales()
    {
        $this->customerAssignment->expects($this->once())
                ->method('assertBelongsToSales');
        $this->execute();
    }
}
