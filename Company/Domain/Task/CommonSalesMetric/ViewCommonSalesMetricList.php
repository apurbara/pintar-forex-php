<?php

namespace Company\Domain\Task\CommonSalesMetric;

use Company\Domain\Model\AdminTaskInCompany;
use Resources\Domain\TaskPayload\ViewPaginationListPayload;

class ViewCommonSalesMetricList implements AdminTaskInCompany
{

    public function __construct(protected CommonSalesMetricRepository $repository)
    {
        
    }

    /**
     * 
     * @param ViewPaginationListPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $result = $this->repository->commonSalesMetricList($payload->paginationSchema);
        $payload->setResult($result);
    }
}
