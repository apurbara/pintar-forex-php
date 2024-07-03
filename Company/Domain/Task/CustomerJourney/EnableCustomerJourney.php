<?php

namespace Company\Domain\Task\CustomerJourney;

use Company\Domain\Model\AdminTaskInCompany;

class EnableCustomerJourney implements AdminTaskInCompany
{

    public function __construct(protected CustomerJourneyRepository $repository)
    {
        
    }

    /**
     * 
     * @param string $payload customerJourneyId
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $this->repository->ofId($payload)
                ->enable();
    }
}
