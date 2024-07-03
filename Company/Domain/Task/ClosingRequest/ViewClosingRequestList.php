<?php

namespace Company\Domain\Task\ClosingRequest;

use Company\Domain\Model\AdminTaskInCompany;
use Resources\Domain\TaskPayload\ViewPaginationListPayload;

class ViewClosingRequestList implements AdminTaskInCompany
{

    public function __construct(protected ClosingRequestRepository $closingRequestRepository)
    {
        
    }

    /**
     * 
     * @param ViewPaginationListPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $result = $this->closingRequestRepository->closingRequestList($payload->paginationSchema);
        $payload->setResult($result);
    }
}
