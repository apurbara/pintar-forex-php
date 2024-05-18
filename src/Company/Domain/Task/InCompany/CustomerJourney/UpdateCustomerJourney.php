<?php

namespace Company\Domain\Task\InCompany\CustomerJourney;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\CustomerJourneyData;

class UpdateCustomerJourney implements AdminTaskInCompany
{

    public function __construct(protected CustomerJourneyRepository $repository)
    {
        
    }

    /**
     * 
     * @param CustomerJourneyData $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $this->repository->ofId($payload->id)
                ->update($payload);
    }
}
