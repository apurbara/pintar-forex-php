<?php

namespace Sales\Domain\Task\SalesActivitySchedule;

use Resources\Domain\TaskPayload\ViewPayload;
use Sales\Domain\Model\Sales;
use Sales\Domain\Task\SalesTask;

class ViewAllNonInitialSchedules implements SalesTask
{
    
    public function __construct(protected SalesActivityScheduleRepository $repository)
    {
    }
    
    /**
     * 
     * @param Sales $sales
     * @param ViewPayload $payload
     * @return void
     */
    public function executeBySales(Sales $sales, $payload): void
    {
        $payload->setResult($this->repository->allNonInitialSchedulesBelongsToSales($sales->getId()));
    }
}
