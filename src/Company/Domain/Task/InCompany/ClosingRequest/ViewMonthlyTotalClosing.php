<?php

namespace Company\Domain\Task\InCompany\ClosingRequest;

use Company\Domain\Model\PersonnelHavingManagerAssignmentTaskInCompany;
use Resources\Domain\TaskPayload\ViewAllListPayload;

class ViewMonthlyTotalClosing implements PersonnelHavingManagerAssignmentTaskInCompany
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
