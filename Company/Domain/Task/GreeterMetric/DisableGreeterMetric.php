<?php

namespace Company\Domain\Task\GreeterMetric;

use Company\Domain\Model\AdminTaskInCompany;

class DisableGreeterMetric implements AdminTaskInCompany
{
    public function __construct(protected GreeterMetricRepository $repository)
    {
        
    }
    
    /**
     * 
     * @param string $payload greeterMetricId
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $this->repository->ofId($payload)->disable();
    }
}
