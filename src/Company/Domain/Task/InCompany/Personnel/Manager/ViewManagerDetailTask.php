<?php

namespace Company\Domain\Task\InCompany\Personnel\Manager;

use Company\Domain\Model\AdminTaskInCompany;
use Resources\Domain\TaskPayload\ViewDetailPayload;

class ViewManagerDetailTask implements AdminTaskInCompany
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
        $payload->setResult($this->managerRepository->managerDetail($payload->id));
    }
}
