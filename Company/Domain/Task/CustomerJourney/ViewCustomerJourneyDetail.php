<?php

namespace Company\Domain\Task\CustomerJourney;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\Manager\SalesTaskInCompany;
use Company\Domain\Model\ManagerTaskInCompany;
use Resources\Domain\TaskPayload\ViewDetailPayload;

class ViewCustomerJourneyDetail implements AdminTaskInCompany, ManagerTaskInCompany, SalesTaskInCompany
{

    public function __construct(protected CustomerJourneyRepository $customerJourneyRepository)
    {
        
    }

    /**
     * 
     * @param ViewDetailPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $result = $this->customerJourneyRepository->aCustomerJourneyDetail($payload->id);
        $payload->setResult($result);
    }
}
