<?php

namespace Company\Domain\Task\InCompany\RecycleRequest;

use Company\Domain\Model\ManagerTaskInCompany;
use Resources\Domain\TaskPayload\ViewDetailPayload;

class ViewRecycleRequestDetail implements ManagerTaskInCompany
{

    public function __construct(protected RecycleRequestRepository $recycleRequestRepository)
    {
        
    }

    /**
     * 
     * @param ViewDetailPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $result = $this->recycleRequestRepository->aRecycleRequest($payload->id);
        $payload->setResult($result);
    }
}
