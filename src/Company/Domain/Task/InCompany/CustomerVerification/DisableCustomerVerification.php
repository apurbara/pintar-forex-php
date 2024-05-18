<?php

namespace Company\Domain\Task\InCompany\CustomerVerification;

use Company\Domain\Model\AdminTaskInCompany;

class DisableCustomerVerification implements AdminTaskInCompany
{

    public function __construct(protected CustomerVerificationRepository $repository)
    {
        
    }

    /**
     * 
     * @param string $payload customerVerificationId
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $this->repository->ofId($payload)
                ->disable();
    }
}
