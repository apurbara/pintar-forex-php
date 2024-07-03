<?php

namespace Company\Domain\Task\ClosingRequest;

use Company\Domain\Model\AdminTaskInCompany;
use Resources\Domain\TaskPayload\ViewDetailPayload;

class ViewClosingRequestDetail implements AdminTaskInCompany
{

    public function __construct(protected ClosingRequestRepository $closingRequestRepository)
    {
        
    }

    /**
     * 
     * @param ViewDetailPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $result = $this->closingRequestRepository->aClosingRequest($payload->id);
        $payload->setResult($result);
    }
}
