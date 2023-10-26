<?php

namespace Company\Domain\Task\InCompany\Personnel;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\PersonnelTaskInCompany;
use Resources\Domain\TaskPayload\ViewDetailPayload;

class ViewPersonnelDetailTask implements AdminTaskInCompany, PersonnelTaskInCompany
{

    public function __construct(protected PersonnelRepository $personnelRepository)
    {
        
    }

    /**
     * 
     * @param ViewDetailPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $payload->setResult($this->personnelRepository->viewPersonnelDetail($payload->id));
    }
}
