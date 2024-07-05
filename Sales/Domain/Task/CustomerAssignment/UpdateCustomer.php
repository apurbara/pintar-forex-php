<?php

namespace Sales\Domain\Task\CustomerAssignment;

use Sales\Domain\Model\Sales;
use Sales\Domain\Task\Dependency\CityRepository;
use Sales\Domain\Task\SalesTask;


class UpdateCustomer implements SalesTask
{

    public function __construct(
            protected CustomerAssignmentRepository $customerAssignmentRepository,
            protected CityRepository $cityRepository)
    {
        
    }

    /**
     * 
     * @param Sales $sales
     * @param UpdateCustomerPayload $payload
     * @return void
     */
    public function executeBySales(Sales $sales, $payload): void
    {
        $area = $this->cityRepository->ofId($payload->customerData->cityId);

        $customerAssignment = $this->customerAssignmentRepository->ofId($payload->id);
        $customerAssignment->assertBelongsToSales($sales);

        $customerAssignment->updateCustomer($area, $payload->customerData);
    }
}
