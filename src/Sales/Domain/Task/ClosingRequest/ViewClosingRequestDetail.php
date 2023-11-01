<?php

namespace Sales\Domain\Task\ClosingRequest;

use Resources\Domain\TaskPayload\ViewDetailPayload;
use Sales\Domain\Model\Personnel\Sales;
use Sales\Domain\Task\SalesTask;

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
