<?php

namespace Manager\Domain\Task\CustomerAssignment;

use Manager\Domain\Model\Manager;
use Manager\Domain\Task\Dependency\CustomerJourneyRepository;
use Manager\Domain\Task\Dependency\CustomerRepository;
use Manager\Domain\Task\ManagerTask;
use Manager\Domain\Task\Sales\SalesRepository;
use Resources\Event\Dispatcher;

class AssignCustomerListToSales implements ManagerTask
{

    public function __construct(
            protected CustomerAssignmentRepository $repository, protected SalesRepository $salesRepository,
            protected CustomerRepository $customerRepository,
            protected CustomerJourneyRepository $customerJourneyRepository,
            protected CustomerAssignmentDistributionServiceInterface $customerAssignmentDistributionService,
            protected Dispatcher $dispatcher)
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
        $initialCustomerJourney = $this->customerJourneyRepository->anInitialCustomerJourney();

        $salesList = [];
        foreach ($payload->getSalesIdList() as $salesId) {
            $sales = $this->salesRepository->ofId($salesId);
            $sales->assertBelongsToManager($manager);
            $this->customerAssignmentDistributionService->registerSales($sales);
            $salesList[] = $sales;
        }

        foreach ($payload->getCustomerIdList() as $customerId) {
            $assignedCustomerId = $this->repository->nextIdentity();
            $customer = $this->customerRepository->ofId($customerId);
            $topPrioritySales = $this->customerAssignmentDistributionService->getTopPrioritySales();
            $assignedCustomer = $topPrioritySales?->receiveCustomerAssignment($assignedCustomerId, $customer,
                    $initialCustomerJourney);
            if (isset($assignedCustomer)) {
                $this->repository->add($assignedCustomer);
            }
        }

        foreach ($salesList as $sales) {
            $this->dispatcher->dispatchEventContainer($sales);
        }
    }
}
