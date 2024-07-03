<?php

namespace Company\Domain\Task\CustomerJourney;

use Company\Domain\Model\Manager\SalesTaskInCompany;
use Company\Domain\Model\ManagerTaskInCompany;
use Resources\Domain\TaskPayload\ViewAllListPayload;

class ViewAllActiveCustomerJourney implements SalesTaskInCompany, ManagerTaskInCompany
{
    public function __construct(protected CustomerJourneyRepository $repository)
    {
    }
    
    /**
     * 
     * @param ViewAllListPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $payload->setResult($this->repository->allActiveCustomerJourney($payload->listSchema));
    }
}
