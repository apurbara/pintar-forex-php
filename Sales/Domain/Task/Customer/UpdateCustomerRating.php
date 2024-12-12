<?php

namespace Sales\Domain\Task\Customer;

use Sales\Domain\Model\Sales;
use Sales\Domain\Task\Dependency\ContainCustomerAssignmentRepository;
use Sales\Domain\Task\SalesTask;

class UpdateCustomerRating implements SalesTask
{

    public function __construct(protected ContainCustomerAssignmentRepository $customerAssignmentRepository)
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
        $assignment = $this->customerAssignmentRepository->ofId($payload->customerAssignmentId);

        $assignment->assertBelongsToSales($sales);
        $assignment->updateCustomerRating($payload->rating);
    }
}
