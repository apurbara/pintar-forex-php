<?php

namespace Sales\Domain\Task\ScheduledSalesActivity;

use Resources\Domain\TaskPayload\ViewDetailPayload;
use Sales\Domain\Model\Personnel\Sales;
use Sales\Domain\Task\SalesTask;

class ViewScheduledSalesActivityDetailTask implements SalesTask
{

    public function __construct(protected ScheduledSalesActivityRepository $scheduledSalesActivityRepository)
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
        $result = $this->scheduledSalesActivityRepository
                ->scheduledSalesActivityBelongsToSalesDetail($sales->getId(), $payload->id);
        $payload->setResult($result);
    }
}
