<?php

namespace Sales\Domain\Task\FactFindingAssignment;

use Sales\Domain\Model\Sales;
use Sales\Domain\Task\SalesTask;

class UpdateCustomerRating implements SalesTask
{

    public function __construct(protected FactFindingAssignmentRepository $repository)
    {
        
    }

    /**
     * 
     * @param Sales $sales
     * @param UpdateCustomerRatingPayload $payload
     * @return void
     */
    public function executeBySales(Sales $sales, $payload): void
    {
        $factFindingAssignment = $this->repository->ofId($payload->id);

        $factFindingAssignment->assertBelongsToSales($sales);
        $factFindingAssignment->updateCustomerRating($payload->rating);
    }
}
