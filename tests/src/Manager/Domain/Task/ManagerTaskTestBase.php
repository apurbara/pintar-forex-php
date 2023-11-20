<?php

namespace Tests\src\Manager\Domain\Task;

use Manager\Domain\Model\Personnel\Manager;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer\ClosingRequest;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer\RecycleRequest;
use Manager\Domain\Task\ClosingRequest\ClosingRequestRepository;
use Manager\Domain\Task\RecycleRequest\RecycleRequestRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestBase;

class ManagerTaskTestBase extends TestBase
{
    protected MockObject $manager;
    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = $this->buildMockOfClass(Manager::class);
    }
    
    //
    protected MockObject $closingRequestRepository;
    protected MockObject $closingRequest;
    protected string $closingRequestId = 'closingRequestId';
    protected function prepareClosingRequestDependency()
    {
        $this->closingRequestRepository = $this->buildMockOfInterface(ClosingRequestRepository::class);
        $this->closingRequest = $this->buildMockOfClass(ClosingRequest::class);
        $this->closingRequestRepository->expects($this->any())
                ->method('ofId')
                ->with($this->closingRequestId)
                ->willReturn($this->closingRequest);
    }
    
    //
    protected MockObject $recycleRequestRepository;
    protected MockObject $recycleRequest;
    protected string $recycleRequestId = 'recycleRequestId';
    protected function prepareRecycleRequestDependency()
    {
        $this->recycleRequestRepository = $this->buildMockOfInterface(RecycleRequestRepository::class);
        $this->recycleRequest = $this->buildMockOfClass(RecycleRequest::class);
        $this->recycleRequestRepository->expects($this->any())
                ->method('ofId')
                ->with($this->recycleRequestId)
                ->willReturn($this->recycleRequest);
    }
    
}
