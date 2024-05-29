<?php

namespace Sales\Domain\Task\BySales\RecycleRequest;

use Resources\Domain\TaskPayload\ViewPaginationListPayload;
use Sales\Domain\Model\Sales;
use Sales\Domain\Task\BySales\SalesTask;

class ViewRecycleRequestListTask implements SalesTask
{

    public function __construct(protected RecycleRequestRepository $recycleRequestRepository)
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
        $result = $this->recycleRequestRepository
                ->recycleRequestListBelongsToSales($sales->getId(), $payload->paginationSchema);
        $payload->setResult($result);
    }
}
