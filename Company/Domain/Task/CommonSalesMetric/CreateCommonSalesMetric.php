<?php

namespace Company\Domain\Task\CommonSalesMetric;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\CommonSalesMetric;
use Company\Domain\Model\CommonSalesMetricData;

class CreateCommonSalesMetric implements AdminTaskInCompany
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
        $payload->setId($this->repository->nextIdentity());
        $commonSalesMetric = new CommonSalesMetric($payload->id, $payload);
        $this->repository->add($commonSalesMetric);
    }
}
