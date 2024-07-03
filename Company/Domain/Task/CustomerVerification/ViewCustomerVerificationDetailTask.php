<?php

namespace Company\Domain\Task\CustomerVerification;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\Manager\SalesTaskInCompany;
use Company\Domain\Model\ManagerTaskInCompany;
use Resources\Domain\TaskPayload\ViewDetailPayload;

class ViewCustomerVerificationDetailTask implements AdminTaskInCompany, ManagerTaskInCompany, SalesTaskInCompany
{

    public function __construct(protected CustomerVerificationRepository $customerVerificationRepository)
    {
        
    }

    /**
     * 
     * @param ViewDetailPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $payload->setResult($this->customerVerificationRepository->customerVerificationDetail($payload->id));
    }
}
