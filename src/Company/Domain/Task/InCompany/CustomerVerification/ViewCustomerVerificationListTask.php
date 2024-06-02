<?php

namespace Company\Domain\Task\InCompany\CustomerVerification;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\ManagerTaskInCompany;
use Company\Domain\Model\SalesTaskInCompany;
use Resources\Domain\TaskPayload\ViewPaginationListPayload;

class ViewCustomerVerificationListTask implements AdminTaskInCompany, ManagerTaskInCompany, SalesTaskInCompany
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
