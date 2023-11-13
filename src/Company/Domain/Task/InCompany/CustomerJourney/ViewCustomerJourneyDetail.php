<?php

namespace Company\Domain\Task\InCompany\CustomerJourney;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\PersonnelTaskInCompany;
use Resources\Domain\TaskPayload\ViewDetailPayload;

class ViewCustomerJourneyDetail implements AdminTaskInCompany, PersonnelTaskInCompany
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
