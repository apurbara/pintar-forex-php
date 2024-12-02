<?php

namespace Company\Domain\Task\GreeterMetric;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\GreeterMetricData;

class UpdateGreeterMetric implements AdminTaskInCompany
{

    public function __construct(protected GreeterMetricRepository $repository)
    {
        
    }

    /**
     * 
     * @param GreeterMetricData $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $greeterMetric = $this->repository->ofId($payload->id);
        $greeterMetric->update($payload);
    }
}
