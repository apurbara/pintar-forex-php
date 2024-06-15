<?php

namespace Company\Domain\Task\InCompany\ClosingRequest;

use Company\Domain\Model\ManagerTaskInCompany;
use Resources\Domain\TaskPayload\ViewSummaryPayload;

class ViewClosingRequestCount implements ManagerTaskInCompany
{
    public function __construct(protected ClosingRequestRepository $repository)
    {
    }
    
    /**
     * 
     * @param ViewSummaryPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $payload->setResult($this->repository->closingRequestCount($payload->searchSchema));
    }
}
