<?php

namespace Company\Domain\Task\CustomerVerification;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\Manager\SalesTaskInCompany;
use Company\Domain\Model\ManagerTaskInCompany;
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
