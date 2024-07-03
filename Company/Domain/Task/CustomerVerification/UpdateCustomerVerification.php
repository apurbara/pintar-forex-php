<?php

namespace Company\Domain\Task\CustomerVerification;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\CustomerVerificationData;

class UpdateCustomerVerification implements AdminTaskInCompany
{

    public function __construct(protected CustomerVerificationRepository $repository)
    {
        
    }

    /**
     * 
     * @param CustomerVerificationData $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $this->repository->ofId($payload->id)
                ->update($payload);
    }
}
