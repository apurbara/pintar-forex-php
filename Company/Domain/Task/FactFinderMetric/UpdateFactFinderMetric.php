<?php

namespace Company\Domain\Task\FactFinderMetric;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\FactFinderMetricData;

class UpdateFactFinderMetric implements AdminTaskInCompany
{

    public function __construct(protected FactFinderMetricRepository $repository)
    {
        
    }

    /**
     * 
     * @param FactFinderMetricData $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $factFinderMetric = $this->repository->ofId($payload->id);
        $factFinderMetric->update($payload);
    }
}
