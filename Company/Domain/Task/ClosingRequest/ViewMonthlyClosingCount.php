<?php

namespace Company\Domain\Task\ClosingRequest;

use Company\Domain\Model\AdminTaskInCompany;
use Resources\Domain\TaskPayload\ViewAllListPayload;

class ViewMonthlyClosingCount implements AdminTaskInCompany
{

    public function __construct(protected ClosingRequestRepository $closingRequestRepository)
    {
        
    }

    /**
     * 
     * @param ViewAllListPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $result = $this->closingRequestRepository->monthlyClosingCount($payload->listSchema);
        $payload->setResult($result);
    }
}
