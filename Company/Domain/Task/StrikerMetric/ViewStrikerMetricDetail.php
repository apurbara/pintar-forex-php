<?php

namespace Company\Domain\Task\StrikerMetric;

use Company\Domain\Model\AdminTaskInCompany;
use Resources\Domain\TaskPayload\ViewDetailPayload;

class ViewStrikerMetricDetail implements AdminTaskInCompany
{

    public function __construct(protected StrikerMetricRepository $repository)
    {
        
    }

    /**
     * 
     * @param ViewDetailPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $result = $this->repository->aStrikerMetric($payload->id);
        $payload->setResult($result);
    }
}
