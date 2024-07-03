<?php

namespace Company\Domain\Task\ClosingRequest;

use Company\Domain\Model\AdminTaskInCompany;
use Resources\Domain\TaskPayload\ViewAllListPayload;

class ViewMonthlyTotalClosing implements AdminTaskInCompany
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
        $result = $this->closingRequestRepository->monthlyTotalClosing($payload->listSchema);
        $payload->setResult($result);
    }
}
