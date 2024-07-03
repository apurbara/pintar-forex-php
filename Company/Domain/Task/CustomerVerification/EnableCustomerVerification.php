<?php

namespace Company\Domain\Task\CustomerVerification;

use Company\Domain\Model\AdminTaskInCompany;

class EnableCustomerVerification implements AdminTaskInCompany
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
                ->enable();
    }
}
