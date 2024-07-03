<?php

namespace Company\Domain\Task\Manager;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\ManagerTaskInCompany;
use Resources\Domain\TaskPayload\ViewDetailPayload;

class ViewManagerDetailTask implements AdminTaskInCompany, ManagerTaskInCompany
{

    public function __construct(protected ManagerRepository $managerRepository)
    {
        
    }

    /**
     * 
     * @param ViewDetailPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $payload->setResult($this->managerRepository->viewManagerDetail($payload->id));
    }
}
