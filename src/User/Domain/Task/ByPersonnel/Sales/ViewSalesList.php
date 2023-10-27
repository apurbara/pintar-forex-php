<?php

namespace User\Domain\Task\ByPersonnel\Sales;

use Resources\Domain\TaskPayload\ViewAllListPayload;
use User\Domain\Model\Personnel;
use User\Domain\Task\ByPersonnel\PersonnelTask;

class ViewSalesList implements PersonnelTask
{

    public function __construct(protected SalesRepository $salesRepository)
    {
        
    }

    /**
     * 
     * @param Personnel $personnel
     * @param ViewAllListPayload $payload
     * @return void
     */
    public function executeByPersonnel(Personnel $personnel, $payload): void
    {
        $result = $this->salesRepository->allSalesListBelongsToPersonnel($personnel->getId(), $payload->listSchema);
        $payload->setResult($result);
    }
}
