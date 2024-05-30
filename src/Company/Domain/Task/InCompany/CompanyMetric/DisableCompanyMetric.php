<?php

namespace Company\Domain\Task\InCompany\CompanyMetric;

use Company\Domain\Model\AdminTaskInCompany;

class DisableCompanyMetric implements AdminTaskInCompany
{

    public function __construct(protected CompanyMetricRepository $repository)
    {
        
    }

    /**
     * 
     * @param string $payload companyMetricId
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $this->repository->ofId($payload)
                ->disable();
    }
}
