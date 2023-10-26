<?php

namespace Company\Domain\Task\InCompany\CustomerVerification;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\PersonnelTaskInCompany;
use Resources\Domain\TaskPayload\ViewPaginationListPayload;

class ViewCustomerVerificationListTask implements AdminTaskInCompany, PersonnelTaskInCompany
{

    public function __construct(protected CustomerVerificationRepository $customerVerificationRepository)
    {
        
    }

    /**
     * 
     * @param ViewPaginationListPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $payload->setResult($this->customerVerificationRepository->customerVerificationList($payload->paginationSchema));
    }
}
