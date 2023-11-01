<?php

namespace Sales\Domain\Task\ClosingRequest;

use Sales\Domain\Model\Personnel\Sales;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\ClosingRequestData;
use Sales\Domain\Task\SalesTask;

class UpdateClosingRequestTask implements SalesTask
{
    public function __construct(protected ClosingRequestRepository $closingRequestRepository)
    {
    }
    
    /**
     * 
     * @param Sales $sales
     * @param ClosingRequestData $payload
     * @return void
     */
    public function executeBySales(Sales $sales, $payload): void
    {
        $closingRequest = $this->closingRequestRepository->ofId($payload->id);
        $closingRequest->assertManageableBySales($sales);
        
        $closingRequest->update($payload);
    }
}
