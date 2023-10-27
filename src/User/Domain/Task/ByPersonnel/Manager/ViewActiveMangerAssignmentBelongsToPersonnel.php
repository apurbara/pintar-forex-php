<?php

namespace User\Domain\Task\ByPersonnel\Manager;

use Resources\Domain\TaskPayload\ViewPayload;
use User\Domain\Model\Personnel;
use User\Domain\Task\ByPersonnel\PersonnelTask;

class ViewActiveMangerAssignmentBelongsToPersonnel implements PersonnelTask
{

    public function __construct(protected ManagerRepository $managerRepository)
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
        $result = $this->managerRepository->activeManagerAssignmentBelongsToPersonnel($personnel->getId());
        $payload->setResult($result);
    }
}
