<?php

namespace Sales\Domain\Task\BySales\CustomerAssignment;

use Sales\Domain\Model\Sales;
use Sales\Domain\Task\Dependency\AreaRepository;
use Sales\Domain\Task\BySales\SalesTask;

class UpdateCustomer implements SalesTask
{

    public function __construct(
            protected CustomerAssignmentRepository $customerAssignmentRepository,
            protected AreaRepository $areaRepository)
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

        $customerAssignment = $this->customerAssignmentRepository->ofId($payload->id);
        $customerAssignment->assertBelongsToSales($sales);

        $customerAssignment->updateCustomer($area, $payload->customerData);
    }
}
