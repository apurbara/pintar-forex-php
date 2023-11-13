<?php

namespace Company\Domain\Task\InCompany\CustomerJourney;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\CustomerJourney;
use Company\Domain\Model\CustomerJourneyData;

class AddCustomerJourney implements AdminTaskInCompany
{

    public function __construct(protected CustomerJourneyRepository $customerJourneyRepository)
    {
        
    }

    /**
     * 
     * @param CustomerJourneyData $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $payload->setId($this->customerJourneyRepository->nextIdentity());
        $customerJourney = new CustomerJourney($payload->id, $payload);
        $this->customerJourneyRepository->add($customerJourney);
    }
}
