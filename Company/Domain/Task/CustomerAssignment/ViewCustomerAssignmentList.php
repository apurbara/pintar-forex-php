<?php

namespace Company\Domain\Task\CustomerAssignment;

use Company\Domain\Model\AdminTaskInCompany;
use Resources\Domain\TaskPayload\ViewPaginationListPayload;

class ViewCustomerAssignmentList implements AdminTaskInCompany
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
