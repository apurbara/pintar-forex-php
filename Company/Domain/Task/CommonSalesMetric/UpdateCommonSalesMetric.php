<?php

namespace Company\Domain\Task\CommonSalesMetric;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\CommonSalesMetricData;

class UpdateCommonSalesMetric implements AdminTaskInCompany
{

    public function __construct(protected CommonSalesMetricRepository $repository)
    {
        
    }

    /**
     * 
     * @param CommonSalesMetricData $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $this->repository->ofId($payload->id)
                ->update($payload);
    }
}
