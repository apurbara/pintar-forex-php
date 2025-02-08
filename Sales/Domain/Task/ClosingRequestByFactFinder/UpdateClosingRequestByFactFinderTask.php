<?php

namespace Sales\Domain\Task\ClosingRequestByFactFinder;

use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\FactFindingAssignment\ClosingRequestByFactFinderData;
use Sales\Domain\Task\SalesTask;

class UpdateClosingRequestByFactFinderTask implements SalesTask
{

    public function __construct(protected ClosingRequestByFactFinderRepository $closingRequestByFactFinderRepository)
    {
        
    }

    /**
     * 
     * @param Sales $sales
     * @param ClosingRequestByFactFinderData $payload
     * @return void
     */
    public function executeBySales(Sales $sales, $payload): void
    {
        $closingRequestByFactFinder = $this->closingRequestByFactFinderRepository->ofId($payload->id);
        $closingRequestByFactFinder->assertManageableBySales($sales);

        $closingRequestByFactFinder->update($payload);
    }
}
