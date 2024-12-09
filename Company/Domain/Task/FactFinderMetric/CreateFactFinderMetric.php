<?php

namespace Company\Domain\Task\FactFinderMetric;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\FactFinderMetric;
use Company\Domain\Model\FactFinderMetricData;

class CreateFactFinderMetric implements AdminTaskInCompany
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
        $payload->setId($this->repository->nextIdentity());
        $factFinderMetric = new FactFinderMetric($payload->id, $payload);
        $this->repository->add($factFinderMetric);
    }
}
