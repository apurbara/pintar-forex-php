<?php

namespace Company\Domain\Task\InCompany\RecycleRequest;

use Company\Domain\Model\Personnel\Sales\CustomerAssignment\RecycleRequestData;
use Company\Domain\Model\PersonnelHavingManagerAssignmentTaskInCompany;

class RejectRecycleRequest implements PersonnelHavingManagerAssignmentTaskInCompany
{

    public function __construct(protected RecycleRequestRepository $recycleRequestRepository)
    {
        
    }

    /**
     * 
     * @param RecycleRequestData $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $this->recycleRequestRepository->ofId($payload->id)->reject($payload);
    }
}
