<?php

namespace Company\Domain\Task\InCompany\CustomerVerification;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\CustomerVerification;
use Company\Domain\Model\CustomerVerificationData;

class AddCustomerVerificationTask implements AdminTaskInCompany
{

    public function __construct(protected CustomerVerificationRepository $customerVerificationRepository)
    {
        
    }

    /**
     * 
     * @param CustomerVerificationData $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $payload->setId($this->customerVerificationRepository->nextIdentity());
        $customerVerification = new CustomerVerification($payload);
        $this->customerVerificationRepository->add($customerVerification);
    }
}
