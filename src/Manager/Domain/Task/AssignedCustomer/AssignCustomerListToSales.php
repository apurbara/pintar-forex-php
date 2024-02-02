<?php

namespace Manager\Domain\Task\AssignedCustomer;

use Manager\Domain\Model\Personnel\Manager;
use Manager\Domain\Service\CustomerAssignmentDistributionCalculatorService;
use Manager\Domain\Task\Customer\CustomerRepository;
use Manager\Domain\Task\CustomerJourney\CustomerJourneyRepository;
use Manager\Domain\Task\ManagerTask;
use Manager\Domain\Task\Sales\SalesRepository;
use Resources\Event\Dispatcher;

class AssignCustomerListToSales implements ManagerTask
{

    public function __construct(
            protected AssignedCustomerRepository $assignedCustomerRepository,
            protected SalesRepository $salesRepository, protected CustomerRepository $customerRepository,
            protected CustomerJourneyRepository $customerJourneyRepository,
            protected CustomerAssignmentDistributionCalculatorService $customerAssignmentDistributionCalculatorService,
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

        foreach ($payload->getSalesIdList() as $salesId) {
            $sales = $this->salesRepository->ofId($salesId);
            $sales->assertManageableByManager($manager);
            $this->customerAssignmentDistributionCalculatorService->registerSales($sales);
        }

        foreach ($payload->getCustomerIdList() as $customerId) {
            $assignedCustomerId = $this->assignedCustomerRepository->nextIdentity();
            $customer = $this->customerRepository->ofId($customerId);
            $topPrioritySales = $this->customerAssignmentDistributionCalculatorService->getTopPrioritySalesForCustomerDistribution();
            $assignedCustomer = $topPrioritySales?->receiveCustomerAssignment($assignedCustomerId, $customer,
                    $initialCustomerJourney);
            if (isset($assignedCustomer)) {
                $this->assignedCustomerRepository->add($assignedCustomer);
            }
        }

        foreach ($this->customerAssignmentDistributionCalculatorService->getSalesList() as $sales) {
            $this->dispatcher->dispatchEventContainer($sales);
        }
    }
}
