<?php

namespace Company\Domain\Task\StrikerMetric;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\StrikerMetricData;

class UpdateStrikerMetric implements AdminTaskInCompany
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
        $strikerMetric = $this->repository->ofId($payload->id);
        $strikerMetric->update($payload);
    }
}
