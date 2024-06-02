<?php

namespace Company\Domain\Task\InCompany\ClosingRequest;

use Company\Domain\Model\ManagerTaskInCompany;
use Company\Domain\Model\Sales\CustomerAssignment\ClosingRequestData;

class RejectClosingRequestTask implements ManagerTaskInCompany
{

    public function __construct(protected ClosingRequestRepository $closingRequestRepository)
    {
        
    }

    /**
     * 
     * @param ClosingRequestData closingRequestId
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $this->closingRequestRepository->ofId($payload->id)->reject($payload);
    }
}
