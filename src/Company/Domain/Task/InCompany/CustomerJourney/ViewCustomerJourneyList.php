<?php

namespace Company\Domain\Task\InCompany\CustomerJourney;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\PersonnelTaskInCompany;
use Resources\Domain\TaskPayload\ViewPaginationListPayload;

class ViewCustomerJourneyList implements AdminTaskInCompany, PersonnelTaskInCompany
{
    public function __construct(protected CustomerJourneyRepository $customerJourneyRepository)
    {
    }
    
    /**
     * 
     * @param ViewPaginationListPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $result = $this->customerJourneyRepository->customerJourneyList($payload->paginationSchema);
        $payload->setResult($result);
    }
}
