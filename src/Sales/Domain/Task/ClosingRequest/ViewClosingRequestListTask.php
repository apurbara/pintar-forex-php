<?php

namespace Sales\Domain\Task\ClosingRequest;

use Resources\Domain\TaskPayload\ViewPaginationListPayload;
use Sales\Domain\Model\Personnel\Sales;
use Sales\Domain\Task\SalesTask;

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
