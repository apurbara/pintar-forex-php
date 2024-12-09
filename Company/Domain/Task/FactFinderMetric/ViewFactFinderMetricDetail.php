<?php

namespace Company\Domain\Task\FactFinderMetric;

use Company\Domain\Model\AdminTaskInCompany;
use Resources\Domain\TaskPayload\ViewDetailPayload;

class ViewFactFinderMetricDetail implements AdminTaskInCompany
{

    public function __construct(protected FactFinderMetricRepository $repository)
    {
        
    }

    /**
     * 
     * @param ViewDetailPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $result = $this->repository->aFactFinderMetric($payload->id);
        $payload->setResult($result);
    }
}
