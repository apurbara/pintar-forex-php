<?php

namespace Company\Domain\Task\CustomerAssignment;

use Company\Domain\Model\AdminTaskInCompany;
use Resources\Domain\TaskPayload\ViewPaginationListPayload;

class ViewCustomerAssignmentList implements AdminTaskInCompany
{

    public function __construct(protected CustomerAssignmentRepository $customerAssignmentRepository)
    {
        
    }

    /**
     * 
     * @param ViewPaginationListPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $result = $this->customerAssignmentRepository
                ->customerAssignmentList($payload->paginationSchema);
        $payload->setResult($result);
    }
}
