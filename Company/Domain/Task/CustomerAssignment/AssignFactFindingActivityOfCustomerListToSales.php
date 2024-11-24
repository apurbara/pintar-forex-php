<?php

namespace Company\Domain\Task\CustomerAssignment;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\Manager\Sales\FactFindingAssignment;
use Company\Domain\Task\Customer\CustomerRepository;
use Company\Domain\Task\Sales\SalesRepository;

class AssignFactFindingActivityOfCustomerListToSales implements AdminTaskInCompany
{

    public function __construct(
            protected FactFindingAssignmentRepository $repository, protected SalesRepository $salesRepository,
            protected CustomerRepository $customerRepository,
            protected CustomerAssignmentDistributionServiceInterface $customerAssignmentDistributionService)
    {
        
    }

    /**
     * 
     * @param AssignCustomerListToSalesPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        foreach ($payload->getSalesIdList() as $salesId) {
            $sales = $this->salesRepository->ofId($salesId);
            $this->customerAssignmentDistributionService->registerSales($sales);
        }

        foreach ($payload->getCustomerIdList() as $customerId) {
            $id = $this->repository->nextIdentity();
            $customer = $this->customerRepository->ofId($customerId);
            $topPrioritySales = $this->customerAssignmentDistributionService->getTopPrioritySales();
            if (!empty($topPrioritySales)) {
                $factFindingAssignment = new FactFindingAssignment($topPrioritySales, $customer, $id);
                $this->repository->add($factFindingAssignment);
            }
        }
    }
}
