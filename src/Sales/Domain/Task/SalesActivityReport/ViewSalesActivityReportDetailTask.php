<?php

namespace Sales\Domain\Task\SalesActivityReport;

use Resources\Domain\TaskPayload\ViewDetailPayload;
use Sales\Domain\Model\Personnel\Sales;
use Sales\Domain\Task\SalesTask;

class ViewSalesActivityReportDetailTask implements SalesTask
{

    public function __construct(protected SalesActivityReportRepository $salesActivityReportRepository)
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
        $result = $this->salesActivityReportRepository
                ->salesActivityReportDetailBelongsToSales($sales->getId(), $payload->id);
        $payload->setResult($result);
    }
}
