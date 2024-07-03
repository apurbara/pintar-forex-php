<?php

namespace Company\Domain\Task\CustomerVerification;

use Company\Domain\Model\Manager\SalesTaskInCompany;
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
