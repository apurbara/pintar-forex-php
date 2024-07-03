<?php

namespace Company\Domain\Task\CompanyMetric;

use Company\Domain\Model\AdminTaskInCompany;
use Resources\Domain\TaskPayload\ViewPaginationListPayload;

class ViewCompanyMetricList implements AdminTaskInCompany
{

    public function __construct(protected CompanyMetricRepository $repository)
    {
        
    }

    /**
     * 
     * @param ViewPaginationListPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $result = $this->repository->companyMetricList($payload->paginationSchema);
        $payload->setResult($result);
    }
}
