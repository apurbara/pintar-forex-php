<?php

namespace Sales\Domain\Task\ScheduledSalesActivity;

use Resources\Domain\TaskPayload\ViewPaginationListPayload;
use Sales\Domain\Model\Personnel\Sales;
use Sales\Domain\Task\SalesTask;

class ViewScheduledSalesActivityListTask implements SalesTask
{

    public function __construct(protected ScheduledSalesActivityRepository $scheduledSalesActivityRepository)
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
        $result = $this->scheduledSalesActivityRepository
                ->scheduledSalesActivityBelongsToSalesList($sales->getId(), $payload->paginationSchema);
        $payload->setResult($result);
    }
}
