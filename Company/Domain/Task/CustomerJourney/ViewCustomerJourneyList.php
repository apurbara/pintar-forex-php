<?php

namespace Company\Domain\Task\CustomerJourney;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\Manager\SalesTaskInCompany;
use Company\Domain\Model\ManagerTaskInCompany;
use Resources\Domain\TaskPayload\ViewPaginationListPayload;

class ViewCustomerJourneyList implements AdminTaskInCompany, ManagerTaskInCompany, SalesTaskInCompany
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
