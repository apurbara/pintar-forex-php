<?php

namespace Sales\Domain\Task\ClosingRequestByFactFinder;

use Resources\Domain\TaskPayload\ViewDetailPayload;
use Sales\Domain\Model\Sales;
use Sales\Domain\Task\SalesTask;

class ViewClosingRequestByFactFinderDetail implements SalesTask
{

    public function __construct(protected ClosingRequestByFactFinderRepository $closingRequestByFactFinderRepository)
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
        $result = $this->closingRequestByFactFinderRepository->aClosingRequestByFactFinderBelongsToSales($sales->getId(), $payload->id);
        $payload->setResult($result);
    }
}
