<?php

namespace Company\Domain\Task\InCompany\RecycleRequest;

use Company\Domain\Model\ManagerTaskInCompany;
use Resources\Domain\TaskPayload\ViewPaginationListPayload;

class ViewRecycleRequestList implements ManagerTaskInCompany
{

    public function __construct(protected RecycleRequestRepository $recycleRequestRepository)
    {
        
    }

    /**
     * 
     * @param ViewPaginationListPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $result = $this->recycleRequestRepository->recycleRequestList($payload->paginationSchema);
        $payload->setResult($result);
    }
}
