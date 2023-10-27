<?php

namespace User\Domain\Task\ByPersonnel\Sales;

use Resources\Domain\TaskPayload\ViewPayload;
use User\Domain\Model\Personnel;
use User\Domain\Task\ByPersonnel\PersonnelTask;

class ViewActiveSalesAssignmentBelongsToPersonnel implements PersonnelTask
{

    public function __construct(protected SalesRepository $salesRepository)
    {
        
    }

    /**
     * 
     * @param Personnel $personnel
     * @param ViewPayload $payload
     * @return void
     */
    public function executeByPersonnel(Personnel $personnel, $payload): void
    {
        $result = $this->salesRepository->activeSalesAssignmentBelongsToPersonnel($personnel->getId());
        $payload->setResult($result);
    }
}
