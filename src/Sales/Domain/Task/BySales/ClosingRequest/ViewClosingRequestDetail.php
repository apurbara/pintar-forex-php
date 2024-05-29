<?php

namespace Sales\Domain\Task\BySales\ClosingRequest;

use Resources\Domain\TaskPayload\ViewDetailPayload;
use Sales\Domain\Model\Sales;
use Sales\Domain\Task\BySales\SalesTask;

class ViewClosingRequestDetail implements SalesTask
{

    public function __construct(protected ClosingRequestRepository $closingRequestRepository)
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
        $result = $this->closingRequestRepository->aClosingRequestBelongsToSales($sales->getId(), $payload->id);
        $payload->setResult($result);
    }
}
