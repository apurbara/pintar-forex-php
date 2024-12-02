<?php

namespace Company\Domain\Task\GreeterMetric;

use Company\Domain\Model\AdminTaskInCompany;
use Resources\Domain\TaskPayload\ViewPaginationListPayload;

class ViewGreeterMetricList implements AdminTaskInCompany
{

    public function __construct(protected GreeterMetricRepository $repository)
    {
        
    }

    /**
     * 
     * @param ViewPaginationListPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $result = $this->repository->greeterMetricList($payload->paginationSchema);
        $payload->setResult($result);
    }
}
