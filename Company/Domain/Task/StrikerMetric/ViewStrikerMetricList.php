<?php

namespace Company\Domain\Task\StrikerMetric;

use Company\Domain\Model\AdminTaskInCompany;
use Resources\Domain\TaskPayload\ViewPaginationListPayload;

class ViewStrikerMetricList implements AdminTaskInCompany
{

    public function __construct(protected StrikerMetricRepository $repository)
    {
        
    }

    /**
     * 
     * @param ViewPaginationListPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $result = $this->repository->strikerMetricList($payload->paginationSchema);
        $payload->setResult($result);
    }
}
