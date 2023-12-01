<?php

use Manager\Application\Listener\DistributeRecycledCustomerFromInHouseSalesListener;
use Manager\Application\Service\Manager\ExecuteManagerTask;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomerData;
use Manager\Domain\Task\AssignedCustomer\AssignCustomerToTopPriorityFreelanceSales;
use SharedContext\Domain\Event\InHouseSalesCustomerAssignmentRecycledEvent;
use Tests\TestBase;

class DistributeRecycledCustomerFromInHouseSalesListenerTest extends TestBase
{
    protected $service;
    protected $task;
    protected $personnelId = 'personnelId', $managerId = 'managerId';
    protected $listener;
    protected $event, $customerId = 'customerId';
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = $this->buildMockOfClass(ExecuteManagerTask::class);
        $this->task = $this->buildMockOfClass(AssignCustomerToTopPriorityFreelanceSales::class);
        $this->listener = new DistributeRecycledCustomerFromInHouseSalesListener($this->service, $this->task, $this->personnelId, $this->managerId);
        //
        $this->event = new InHouseSalesCustomerAssignmentRecycledEvent($this->customerId);
    }
    
    //
    protected function handle()
    {
        $this->listener->handle($this->event);
    }
    public function test_handle_executeService()
    {
        $payload = (new AssignedCustomerData())->setCustomerId($this->customerId);
        $this->service->expects($this->once())
                ->method('excute')
                ->with($this->personnelId, $this->managerId, $this->task, $payload);
        $this->handle();
    }
}
