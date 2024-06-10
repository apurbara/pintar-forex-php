<?php

namespace Company\Domain\Task\InCompany\CustomerVerification;

use Company\Domain\Model\SalesTaskInCompany;
use Resources\Domain\TaskPayload\ViewAllListPayload;

class ViewAllActiveCustomerVerification implements SalesTaskInCompany
{

    public function __construct(protected CustomerVerificationRepository $repository)
    {
        
    }

    /**
     * 
     * @param ViewAllListPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $result = $this->repository->allCustomerVerification($payload->listSchema);
        $payload->setResult($result);
    }
}
