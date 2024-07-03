<?php

namespace Company\Domain\Task\ClosingRequest;

use Company\Domain\Model\AdminTaskInCompany;
use Resources\Domain\TaskPayload\ViewSummaryPayload;

class ViewClosingRequestCount implements AdminTaskInCompany
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
