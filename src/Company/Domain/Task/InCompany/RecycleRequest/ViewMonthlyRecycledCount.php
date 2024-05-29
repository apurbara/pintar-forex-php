<?php

namespace Company\Domain\Task\InCompany\RecycleRequest;

use Company\Domain\Model\PersonnelHavingManagerAssignmentTaskInCompany;
use Resources\Domain\TaskPayload\ViewAllListPayload;

class ViewMonthlyRecycledCount implements PersonnelHavingManagerAssignmentTaskInCompany
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
