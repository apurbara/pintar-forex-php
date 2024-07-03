<?php

namespace Company\Domain\Task\RecycleRequest;

use Company\Domain\Model\AdminTaskInCompany;
use Resources\Domain\TaskPayload\ViewSummaryPayload;

class ViewRecycleRequestCount implements AdminTaskInCompany
{
    public function __construct(protected RecycleRequestRepository $repository)
    {
    }
    
    /**
     * 
     * @param ViewSummaryPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $result = $this->repository->recycleRequestCount($payload->searchSchema);
        $payload->setResult($result);
    }
}
