<?php

namespace Manager\Domain\Task\GreetingAssignment;

use Manager\Domain\Model\Manager;
use Manager\Domain\Model\Manager\Sales\GreetingAssignment;
use Manager\Domain\Task\CustomerAssignment\AssignCustomerListToSalesPayload;
use Manager\Domain\Task\CustomerAssignment\CustomerAssignmentDistributionServiceInterface;
use Manager\Domain\Task\Dependency\CustomerRepository;
use Manager\Domain\Task\ManagerTask;
use Manager\Domain\Task\Sales\SalesRepository;

class AssignGreetingActivityOfCustomerListToSales implements ManagerTask
{

    public function __construct(
            protected GreetingAssignmentRepository $repository, protected SalesRepository $salesRepository,
            protected CustomerRepository $customerRepository,
            protected CustomerAssignmentDistributionServiceInterface $customerAssignmentDistributionService)
    {
        
    }

    /**
     * 
     * @param Manager $manager
     * @param AssignCustomerListToSalesPayload $payload
     * @return void
     */
    public function executeByManager(Manager $manager, $payload): void
    {
        foreach ($payload->getSalesIdList() as $salesId) {
            $sales = $this->salesRepository->ofId($salesId);
            $sales->assertBelongsToManager($manager);
            $this->customerAssignmentDistributionService->registerSales($sales);
        }
        foreach ($payload->getCustomerIdList() as $customerId) {
            $customer = $this->customerRepository->ofId($customerId);
            $topPrioritySales = $this->customerAssignmentDistributionService->getTopPrioritySales();
            if (isset($topPrioritySales)) {
                $greetingAssignment = new GreetingAssignment($topPrioritySales, $customer, $this->repository->nextIdentity());
                $this->repository->add($greetingAssignment);
            }
        }
    }
}
