<?php

namespace Company\Domain\Task\GreeterMetric;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\GreeterMetric;
use Company\Domain\Model\GreeterMetricData;

class CreateGreeterMetric implements AdminTaskInCompany
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
        $payload->setId($this->repository->nextIdentity());
        $greeterMetric = new GreeterMetric($payload->id, $payload);
        $this->repository->add($greeterMetric);
    }
}
