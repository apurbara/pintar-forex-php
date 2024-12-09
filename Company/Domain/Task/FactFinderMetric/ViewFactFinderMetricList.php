<?php

namespace Company\Domain\Task\FactFinderMetric;

use Company\Domain\Model\AdminTaskInCompany;
use Resources\Domain\TaskPayload\ViewPaginationListPayload;

class ViewFactFinderMetricList implements AdminTaskInCompany
{

    public function __construct(protected FactFinderMetricRepository $repository)
    {
        
    }

    /**
     * 
     * @param ViewPaginationListPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $result = $this->repository->factFinderMetricList($payload->paginationSchema);
        $payload->setResult($result);
    }
}
