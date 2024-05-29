<?php

namespace Sales\Domain\Task\BySales\ClosingRequest;

use Resources\Domain\TaskPayload\ViewPaginationListPayload;
use Sales\Domain\Model\Sales;
use Sales\Domain\Task\BySales\SalesTask;

class ViewClosingRequestListTask implements SalesTask
{

    public function __construct(protected ClosingRequestRepository $closingRequestRepository)
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
        $result = $this->closingRequestRepository
                ->closingRequestListBelongsToSales($sales->getId(), $payload->paginationSchema);
        $payload->setResult($result);
    }
}
