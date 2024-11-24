<?php

namespace Sales\Domain\Task\StrikingAssignment;

use Resources\Domain\TaskPayload\ViewPaginationListPayload;
use Sales\Domain\Model\Sales;
use Sales\Domain\Task\SalesTask;

class ViewStrikingAssignmentList implements SalesTask
{

    public function __construct(protected StrikingAssignmentRepository $strikingAssignmentRepository)
    {
        
    }

    /**
     * 
     * @param Sales $sales
     * @param ViewPaginationListPayload $payload
     * @return void
     */
    public function executeBySales(Sales $sales, $payload): void
    {
        $payload->setResult($this->strikingAssignmentRepository->strikingAssignmentListBelongsToSales($sales->getId(),
                        $payload->paginationSchema));
    }
}
