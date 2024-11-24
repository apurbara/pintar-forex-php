<?php

namespace Sales\Domain\Task\FactFindingAssignment;

use Resources\Event\Dispatcher;
use Sales\Domain\Model\Sales;
use Sales\Domain\Task\Dependency\CustomerVerificationRepository;
use Sales\Domain\Task\SalesTask;

class MarkCustomerVerified implements SalesTask
{

    public function __construct(
            protected FactFindingAssignmentRepository $repository,
            protected CustomerVerificationRepository $customerVerificationRepository, protected Dispatcher $dispatcher)
    {
        
    }

    /**
     * 
     * @param Sales $sales
     * @param string $payload factFindingAssignmentId
     * @return void
     */
    public function executeBySales(Sales $sales, $payload): void
    {
        $factFindingAssignment = $this->repository->ofId($payload);
        $allActiveCustomerVerifications = $this->customerVerificationRepository->allActiveCustomerVerification();

        $factFindingAssignment->assertBelongsToSales($sales);
        $factFindingAssignment->markCustomerVerified($allActiveCustomerVerifications);
        
        $this->dispatcher->dispatchEventContainer($factFindingAssignment);
    }
}
