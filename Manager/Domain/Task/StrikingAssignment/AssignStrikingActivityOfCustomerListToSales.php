<?php

namespace Manager\Domain\Task\StrikingAssignment;

use Manager\Domain\Model\Manager;
use Manager\Domain\Model\Manager\Sales\StrikingAssignment;
use Manager\Domain\Task\CustomerAssignment\AssignCustomerListToSalesPayload;
use Manager\Domain\Task\CustomerAssignment\CustomerAssignmentDistributionServiceInterface;
use Manager\Domain\Task\Dependency\CustomerJourneyRepository;
use Manager\Domain\Task\Dependency\CustomerRepository;
use Manager\Domain\Task\ManagerTask;
use Manager\Domain\Task\Sales\SalesRepository;


class AssignStrikingActivityOfCustomerListToSales implements ManagerTask
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
    public function executeByManager(Manager $manager, $payload): void
    {
        $customerJourney = $this->customerJourneyRepository->anInitialCustomerJourney();
        foreach ($payload->getSalesIdList() as $salesId) {
            $sales = $this->salesRepository->ofId($salesId);
            $sales->assertBelongsToManager($manager);
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
