<?php

namespace User\Domain\Task\ByPersonnel\Manager;

use Resources\Domain\TaskPayload\ViewPaginationListPayload;
use User\Domain\Model\Personnel;
use User\Domain\Task\ByPersonnel\PersonnelTask;

class ViewMangerAssignmentList implements PersonnelTask
{

    public function __construct(protected ManagerRepository $managerRepository)
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
        $result = $this->managerRepository->managerAssignmentListBelongsToPersonnel(
                $personnel->getId(), $payload->paginationSchema);
        $payload->setResult($result);
    }
}
