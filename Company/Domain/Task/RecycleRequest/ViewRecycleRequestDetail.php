<?php

namespace Company\Domain\Task\RecycleRequest;

use Company\Domain\Model\AdminTaskInCompany;
use Resources\Domain\TaskPayload\ViewDetailPayload;

class ViewRecycleRequestDetail implements AdminTaskInCompany
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
