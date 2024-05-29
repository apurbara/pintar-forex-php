<?php

namespace Sales\Domain\Task\BySales\SalesActivitySchedule;

use Resources\Domain\TaskPayload\ViewAllListPayload;
use Sales\Domain\Model\Sales;
use Sales\Domain\Task\BySales\SalesTask;

class ViewSalesActivityScheduleSummary implements SalesTask
{

    public function __construct(protected SalesActivityScheduleRepository $repository)
    {
        
    }

    /**
     * 
     * @param Sales $sales
     * @param ViewAllListPayload $payload
     * @return void
     */
    public function executeBySales(Sales $sales, $payload): void
    {
        $result = $this->repository->salesActivityScheduleSummaryBelongsToSales($sales->getId(), $payload->listSchema);
        $payload->setResult($result);
    }
}
