<?php

namespace Company\Domain\Task\CustomerAssignment;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Task\Customer\CustomerRepository;
use Company\Domain\Task\CustomerJourney\CustomerJourneyRepository;
use Company\Domain\Task\Sales\SalesRepository;
use Resources\Event\Dispatcher;

class AssignCustomerListToSales implements AdminTaskInCompany
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
     * @param AssignCustomerListToSalesPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $initialCustomerJourney = $this->customerJourneyRepository->anInitialCustomerJourney();

        $salesList = [];
        foreach ($payload->getSalesIdList() as $salesId) {
            $sales = $this->salesRepository->ofId($salesId);
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
