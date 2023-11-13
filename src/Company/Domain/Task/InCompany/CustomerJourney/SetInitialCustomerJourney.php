<?php

namespace Company\Domain\Task\InCompany\CustomerJourney;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\CustomerJourney;
use Company\Domain\Model\CustomerJourneyData;

class SetInitialCustomerJourney implements AdminTaskInCompany
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
        $payload->setInitial();
        $customerJourney = $this->customerJourneyRepository->anInitialCustomerJourney();
        if ($customerJourney) {
            $customerJourney->update($payload);
        } else {
            $customerJourney = new CustomerJourney($this->customerJourneyRepository->nextIdentity(), $payload);
            $this->customerJourneyRepository->add($customerJourney);
        }
    }
}
