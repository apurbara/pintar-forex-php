<?php

namespace Company\Domain\Task\StrikerMetric;

use Company\Domain\Model\AdminTaskInCompany;

class EnableStrikerMetric implements AdminTaskInCompany
{
    public function __construct(protected StrikerMetricRepository $repository)
    {
        
    }
    
    /**
     * 
     * @param string $payload strikerMetricId
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $this->repository->ofId($payload)->enable();
    }
}
