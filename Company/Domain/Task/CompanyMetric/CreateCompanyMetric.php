<?php

namespace Company\Domain\Task\CompanyMetric;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\CompanyMetric;
use Company\Domain\Model\CompanyMetricData;

class CreateCompanyMetric implements AdminTaskInCompany
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
        $payload->setId($this->repository->nextIdentity());
        $companyMetric = new CompanyMetric($payload->id, $payload);
        $this->repository->add($companyMetric);
    }
}
