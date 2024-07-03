<?php

namespace Company\Domain\Task\CompanyMetric;

use Company\Domain\Model\AdminTaskInCompany;
use Resources\Domain\TaskPayload\ViewDetailPayload;

class ViewCompanyMetricDetail implements AdminTaskInCompany
{

    public function __construct(protected CompanyMetricRepository $repository)
    {
        
    }

    /**
     * 
     * @param ViewDetailPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $result = $this->repository->aCompanyMetric($payload->id);
        $payload->setResult($result);
    }
}
