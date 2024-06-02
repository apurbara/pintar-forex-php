<?php

namespace Company\Domain\Task\InCompany\RecycleRequest;

use Company\Domain\Model\ManagerTaskInCompany;
use Resources\Domain\TaskPayload\ViewAllListPayload;

class ViewMonthlyRecycledCount implements ManagerTaskInCompany
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
