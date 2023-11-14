<?php

namespace Sales\Domain\Task\SalesActivitySchedule;

use Resources\Domain\TaskPayload\ViewSummaryPayload;
use Sales\Domain\Model\Personnel\Sales;
use Sales\Domain\Task\SalesTask;

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
