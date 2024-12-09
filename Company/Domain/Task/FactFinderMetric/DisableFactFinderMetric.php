<?php

namespace Company\Domain\Task\FactFinderMetric;

use Company\Domain\Model\AdminTaskInCompany;

class DisableFactFinderMetric implements AdminTaskInCompany
{
    public function __construct(protected FactFinderMetricRepository $repository)
    {
        
    }
    
    /**
     * 
     * @param string $payload factFinderMetricId
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $this->repository->ofId($payload)->disable();
    }
}
