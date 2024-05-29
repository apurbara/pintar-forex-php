<?php

namespace Company\Domain\Task\InCompany\ClosingRequest;

use Company\Domain\Model\Personnel\Sales\CustomerAssignment\ClosingRequestData;
use Company\Domain\Model\PersonnelHavingManagerAssignmentTaskInCompany;

class AcceptClosingRequestTask implements PersonnelHavingManagerAssignmentTaskInCompany
{
    public function __construct(protected ClosingRequestRepository $closingRequestRepository)
    {
    }
    
    /**
     * 
     * @param ClosingRequestData $payload closingRequestId
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $this->closingRequestRepository->ofId($payload->id)->accept($payload);
    }
}
