<?php

namespace Company\Domain\Task\InCompany\RecycleRequest;

use Company\Domain\Model\ManagerTaskInCompany;
use Company\Domain\Model\Sales\CustomerAssignment\RecycleRequestData;

class RejectRecycleRequest implements ManagerTaskInCompany
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
