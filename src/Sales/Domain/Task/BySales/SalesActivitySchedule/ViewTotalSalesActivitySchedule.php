<?php

namespace Sales\Domain\Task\BySales\SalesActivitySchedule;

use Resources\Domain\TaskPayload\ViewSummaryPayload;
use Sales\Domain\Model\Sales;
use Sales\Domain\Task\BySales\SalesTask;

class ViewTotalSalesActivitySchedule implements SalesTask
{

    public function __construct(protected SalesActivityScheduleRepository $scheduledSalesActivityRepository)
    {
        
    }

    /**
     * 
     * @param Sales $sales
     * @param ViewSummaryPayload $payload
     * @return void
     */
    public function executeBySales(Sales $sales, $payload): void
    {
        $result = $this->scheduledSalesActivityRepository
                ->totalSalesActivityScheduleBelongsToSales($sales->getId(), $payload->searchSchema);
        $payload->setResult($result);
    }
}
