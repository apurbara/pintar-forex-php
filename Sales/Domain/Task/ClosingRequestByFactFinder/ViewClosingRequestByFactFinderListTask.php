<?php

namespace Sales\Domain\Task\ClosingRequestByFactFinder;

use Resources\Domain\TaskPayload\ViewPaginationListPayload;
use Sales\Domain\Model\Sales;
use Sales\Domain\Task\SalesTask;

class ViewClosingRequestByFactFinderListTask implements SalesTask
{

    public function __construct(protected ClosingRequestByFactFinderRepository $closingRequestByFactFinderRepository)
    {
        
    }

    /**
     * 
     * @param Sales $sales
     * @param ViewPaginationListPayload $payload
     * @return void
     */
    public function executeBySales(Sales $sales, $payload): void
    {
        $result = $this->closingRequestByFactFinderRepository
                ->closingRequestByFactFinderListBelongsToSales($sales->getId(), $payload->paginationSchema);
        $payload->setResult($result);
    }
}
