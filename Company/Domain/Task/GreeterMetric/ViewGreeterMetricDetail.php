<?php

namespace Company\Domain\Task\GreeterMetric;

use Company\Domain\Model\AdminTaskInCompany;
use Resources\Domain\TaskPayload\ViewDetailPayload;

class ViewGreeterMetricDetail implements AdminTaskInCompany
{

    public function __construct(protected GreeterMetricRepository $repository)
    {
        
    }

    /**
     * 
     * @param ViewDetailPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $result = $this->repository->aGreeterMetric($payload->id);
        $payload->setResult($result);
    }
}
