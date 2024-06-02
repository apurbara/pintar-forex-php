<?php

namespace Company\Domain\Task\InCompany\ClosingRequest;

use Company\Domain\Model\ManagerTaskInCompany;
use Company\Domain\Model\Sales\CustomerAssignment\ClosingRequestData;

class AcceptClosingRequestTask implements ManagerTaskInCompany
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
