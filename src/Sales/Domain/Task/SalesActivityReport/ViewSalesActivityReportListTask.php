<?php

namespace Sales\Domain\Task\SalesActivityReport;

use Resources\Domain\TaskPayload\ViewPaginationListPayload;
use Sales\Domain\Model\Personnel\Sales;
use Sales\Domain\Task\SalesTask;

class ViewSalesActivityReportListTask implements SalesTask
{

    public function __construct(protected SalesActivityReportRepository $salesActivityReportRepository)
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
        $result = $this->salesActivityReportRepository
                ->salesActivityReportListBelongsToSales($sales->getId(), $payload->paginationSchema);
        $payload->setResult($result);
    }
}
