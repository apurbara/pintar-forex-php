<?php

namespace Sales\Domain\Task\AssignedCustomer;

use Sales\Domain\Model\Personnel\Sales;
use Sales\Domain\Task\Area\AreaRepository;
use Sales\Domain\Task\SalesTask;

class UpdateCustomer implements SalesTask
{

    public function __construct(
            protected AssignedCustomerRepository $assignedCustomerRepository, protected AreaRepository $areaRepository)
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
        $area = $this->areaRepository->ofId($payload->customerData->areaId);
        
        $customerAssignment = $this->assignedCustomerRepository->ofId($payload->id);
        $customerAssignment->assertBelongsToSales($sales);
        
        $customerAssignment->updateCustomer($area, $payload->customerData);
    }
}
