<?php

namespace Sales\Domain\Task\Customer;

use Sales\Domain\Model\Sales;
use Sales\Domain\Task\Dependency\CityRepository;
use Sales\Domain\Task\Dependency\ContainCustomerAssignmentRepository;
use Sales\Domain\Task\SalesTask;

class UpdateCustomer implements SalesTask
{

    public function __construct(
            protected ContainCustomerAssignmentRepository $customerAssignmentRepository,
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
        $city = null;
        if (isset($payload->customerData->cityId)) {
            $city = $this->cityRepository->ofId($payload->customerData->cityId);
        }

        $assignment = $this->customerAssignmentRepository->ofId($payload->id);
        $assignment->assertBelongsToSales($sales);

        $assignment->updateCustomer($payload->customerData, $city);
    }
}
