<?php

namespace Company\Domain\Task\RecycleRequest;

use Company\Domain\Model\AdminTaskInCompany;
use Resources\Domain\TaskPayload\ViewAllListPayload;

class ViewMonthlyRecycledCount implements AdminTaskInCompany
{

    public function __construct(protected RecycleRequestRepository $recycleRequestRepository)
    {
        
    }

    /**
     * 
     * @param ViewAllListPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $result = $this->recycleRequestRepository->monthlyRecycledCount($payload->listSchema);
        $payload->setResult($result);
    }
}
