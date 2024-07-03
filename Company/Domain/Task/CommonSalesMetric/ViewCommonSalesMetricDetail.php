<?php

namespace Company\Domain\Task\CommonSalesMetric;

use Company\Domain\Model\AdminTaskInCompany;
use Resources\Domain\TaskPayload\ViewDetailPayload;

class ViewCommonSalesMetricDetail implements AdminTaskInCompany
{

    public function __construct(protected CommonSalesMetricRepository $repository)
    {
        
    }

    /**
     * 
     * @param ViewDetailPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $result = $this->repository->aCommonSalesMetric($payload->id);
        $payload->setResult($result);
    }
}
