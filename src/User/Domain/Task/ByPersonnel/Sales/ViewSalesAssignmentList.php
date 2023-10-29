<?php

namespace User\Domain\Task\ByPersonnel\Sales;

use Resources\Domain\TaskPayload\ViewPaginationListPayload;
use User\Domain\Model\Personnel;
use User\Domain\Task\ByPersonnel\PersonnelTask;

class ViewSalesAssignmentList implements PersonnelTask
{

    public function __construct(protected SalesRepository $salesRepository)
    {
        
    }

    /**
     * 
     * @param Personnel $personnel
     * @param ViewPaginationListPayload $payload
     * @return void
     */
    public function executeByPersonnel(Personnel $personnel, $payload): void
    {
        $result = $this->salesRepository->salesAssignmentListBelongsToPersonnel(
                $personnel->getId(), $payload->paginationSchema);
        $payload->setResult($result);
    }
}
