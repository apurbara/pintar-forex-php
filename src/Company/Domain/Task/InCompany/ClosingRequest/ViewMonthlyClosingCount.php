<?php

namespace Company\Domain\Task\InCompany\ClosingRequest;

use Company\Domain\Model\ManagerTaskInCompany;
use Resources\Domain\TaskPayload\ViewAllListPayload;

class ViewMonthlyClosingCount implements ManagerTaskInCompany
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
