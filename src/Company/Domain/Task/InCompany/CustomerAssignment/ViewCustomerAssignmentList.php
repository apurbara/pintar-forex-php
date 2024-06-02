<?php

namespace Company\Domain\Task\InCompany\CustomerAssignment;

use Company\Domain\Model\ManagerTaskInCompany;
use Resources\Domain\TaskPayload\ViewPaginationListPayload;

class ViewCustomerAssignmentList implements ManagerTaskInCompany
{

    public function __construct(protected CustomerAssignmentRepository $assignedCustomerRepository)
    {
        
    }

    /**
     * 
     * @param ViewPaginationListPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $result = $this->assignedCustomerRepository
                ->customerAssignmentList($payload->paginationSchema);
        $payload->setResult($result);
    }
}
