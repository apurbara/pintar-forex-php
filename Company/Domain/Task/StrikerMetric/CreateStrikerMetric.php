<?php

namespace Company\Domain\Task\StrikerMetric;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\StrikerMetric;
use Company\Domain\Model\StrikerMetricData;

class CreateStrikerMetric implements AdminTaskInCompany
{

    public function __construct(protected StrikerMetricRepository $repository)
    {
        
    }

    /**
     * 
     * @param StrikerMetricData $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $payload->setId($this->repository->nextIdentity());
        $strikerMetric = new StrikerMetric($payload->id, $payload);
        $this->repository->add($strikerMetric);
    }
}
