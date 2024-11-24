<?php

namespace Manager\Domain\Task\FactFindingAssignment;

use Manager\Domain\Model\Manager;
use Manager\Domain\Model\Manager\Sales\FactFindingAssignment;
use Manager\Domain\Task\CustomerAssignment\AssignCustomerListToSalesPayload;
use Manager\Domain\Task\CustomerAssignment\CustomerAssignmentDistributionServiceInterface;
use Manager\Domain\Task\Dependency\CustomerRepository;
use Manager\Domain\Task\ManagerTask;
use Manager\Domain\Task\Sales\SalesRepository;

class AssignFactFindingActivityOfCustomerListToSales implements ManagerTask
{

    public function __construct(
            protected FactFindingAssignmentRepository $repository, protected SalesRepository $salesRepository,
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
                $factFindingAssignment = new FactFindingAssignment($topPrioritySales, $customer, $this->repository->nextIdentity());
                $this->repository->add($factFindingAssignment);
            }
        }
    }
}
