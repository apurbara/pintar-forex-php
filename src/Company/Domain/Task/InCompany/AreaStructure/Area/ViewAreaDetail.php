<?php

namespace Company\Domain\Task\InCompany\AreaStructure\Area;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\PersonnelTaskInCompany;
use Resources\Domain\TaskPayload\ViewDetailPayload;

class ViewAreaDetail implements AdminTaskInCompany, PersonnelTaskInCompany
{

    public function __construct(protected AreaRepository $areaRepository)
    {
        
    }

    /**
     * 
     * @param ViewDetailPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $payload->setResult($this->areaRepository->viewAreaDetail($payload->id));
    }
}
