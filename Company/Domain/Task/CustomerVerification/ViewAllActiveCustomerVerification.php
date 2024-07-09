<?php

namespace Company\Domain\Task\CustomerVerification;

use Company\Domain\Model\Manager\SalesTaskInCompany;
use Company\Domain\Model\ManagerTaskInCompany;
use Resources\Domain\TaskPayload\ViewAllListPayload;

class ViewAllActiveCustomerVerification implements SalesTaskInCompany, ManagerTaskInCompany
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
        $searchSchema = $payload->listSchema;
        $searchSchema['filters'][] = ['column' => 'CustomerVerification.disabled', "value" => false ];
        $result = $this->repository->allCustomerVerification($searchSchema);
        $payload->setResult($result);
    }
}
