<?php

namespace Sales\Domain\Task\BySales\RecycleRequest;

use Resources\Domain\TaskPayload\ViewDetailPayload;
use Sales\Domain\Model\Sales;
use Sales\Domain\Task\BySales\SalesTask;

class ViewRecycleRequestDetail implements SalesTask
{

    public function __construct(protected RecycleRequestRepository $recycleRequestRepository)
    {
        
    }

    /**
     * 
     * @param Sales $sales
     * @param ViewDetailPayload $payload
     * @return void
     */
    public function executeBySales(Sales $sales, $payload): void
    {
        $result = $this->recycleRequestRepository->aRecycleRequestBelongsToSales($sales->getId(), $payload->id);
        $payload->setResult($result);
    }
}
