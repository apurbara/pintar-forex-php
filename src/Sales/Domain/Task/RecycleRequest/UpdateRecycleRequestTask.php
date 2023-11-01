<?php

namespace Sales\Domain\Task\RecycleRequest;

use Sales\Domain\Model\Personnel\Sales;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\RecycleRequestData;
use Sales\Domain\Task\SalesTask;

class UpdateRecycleRequestTask implements SalesTask
{
    public function __construct(protected RecycleRequestRepository $recycleRequestRepository)
    {
    }
    
    /**
     * 
     * @param Sales $sales
     * @param RecycleRequestData $payload
     * @return void
     */
    public function executeBySales(Sales $sales, $payload): void
    {
        $recycleRequest = $this->recycleRequestRepository->ofId($payload->id);
        $recycleRequest->assertManageableBySales($sales);
        
        $recycleRequest->update($payload);
    }
}
