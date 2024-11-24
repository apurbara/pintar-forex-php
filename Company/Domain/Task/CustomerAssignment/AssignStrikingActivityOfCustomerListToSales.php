<?php

namespace Company\Domain\Task\CustomerAssignment;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\Manager\Sales\StrikingAssignment;
use Company\Domain\Task\Customer\CustomerRepository;
use Company\Domain\Task\CustomerJourney\CustomerJourneyRepository;
use Company\Domain\Task\Sales\SalesRepository;

class AssignStrikingActivityOfCustomerListToSales implements AdminTaskInCompany
{

    public function __construct(
            protected StrikingAssignmentRepository $repository, protected SalesRepository $salesRepository,
            protected CustomerRepository $customerRepository,
            protected CustomerAssignmentDistributionServiceInterface $customerAssignmentDistributionService,
            protected CustomerJourneyRepository $customerJourneyRepository)
    {
        
    }

    /**
     * 
     * @param AssignCustomerListToSalesPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $customerJourney = $this->customerJourneyRepository->anInitialCustomerJourney();
        foreach ($payload->getSalesIdList() as $salesId) {
            $sales = $this->salesRepository->ofId($salesId);
            $this->customerAssignmentDistributionService->registerSales($sales);
        }

        foreach ($payload->getCustomerIdList() as $customerId) {
            $id = $this->repository->nextIdentity();
            $customer = $this->customerRepository->ofId($customerId);
            $topPrioritySales = $this->customerAssignmentDistributionService->getTopPrioritySales();
            if (!empty($topPrioritySales)) {
                $strikingAssignment = new StrikingAssignment($topPrioritySales, $customer, $id, $customerJourney);
                $this->repository->add($strikingAssignment);
            }
        }
    }
}
