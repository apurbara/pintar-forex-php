<?php

namespace Company\Domain\Task\InCompany\ClosingRequest;

use Company\Domain\Model\PersonnelHavingManagerAssignmentTaskInCompany;
use Resources\Domain\TaskPayload\ViewDetailPayload;

class ViewClosingRequestDetail implements PersonnelHavingManagerAssignmentTaskInCompany
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
