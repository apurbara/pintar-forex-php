<?php

namespace Company\Domain\Task\InCompany\CompanyMetric;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\CompanyMetricData;

class UpdateCompanyMetric implements AdminTaskInCompany
{

    public function __construct(protected CompanyMetricRepository $repository)
    {
        
    }

    /**
     * 
     * @param CompanyMetricData $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $this->repository->ofId($payload->id)
                ->update($payload);
    }
}
