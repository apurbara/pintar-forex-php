<?php

namespace Sales\Domain\Task\CommonSalesMetricSummary;

use Resources\Domain\TaskPayload\ViewPayload;
use Sales\Domain\Model\Sales;
use Sales\Domain\Task\SalesTask;
use Sales\Domain\Task\Dependency\CommonSalesMetricRepository;

class ViewAllCommonSalesMetricSummary implements SalesTask
{

    public function __construct(
            protected CommonSalesMetricSummaryRepository $repository, protected CommonSalesMetricRepository $commonSalesRepository)
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
        $result = [];
        foreach ($this->commonSalesRepository->allActive() as $commonSalesMetric) {
            $result[] = $this->repository->summaryOfCommonSalesMetricBelongsToSales($sales->getId(), $commonSalesMetric);
        }
        $payload->setResult($result);
    }
}
