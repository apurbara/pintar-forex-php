<?php

namespace Sales\Domain\Task\BySales\SalesActivitySchedule;

use Sales\Domain\Model\Sales;
use Sales\Domain\Task\BySales\SalesTask;

class ViewAllNonInitialSchedulesInMonth implements SalesTask
{
    public function __construct(protected SalesActivityScheduleRepository $repository)
    {
    }
    
    /**
     * 
     * @param Sales $sales
     * @param ViewAllNonInitialSchedulesInMonthPayload $payload
     * @return void
     */
    public function executeBySales(Sales $sales, $payload): void
    {
        $result = $this->repository
                ->allNonInitialSchedulesInMonthBelongsToSales($sales->getId(), $payload->year, $payload->month);
        $payload->setResult($result);
    }
}
