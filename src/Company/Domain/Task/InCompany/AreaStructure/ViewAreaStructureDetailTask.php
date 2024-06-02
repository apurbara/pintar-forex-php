<?php

namespace Company\Domain\Task\InCompany\AreaStructure;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\ManagerTaskInCompany;
use Company\Domain\Model\SalesTaskInCompany;
use Resources\Domain\TaskPayload\ViewDetailPayload;

class ViewAreaStructureDetailTask implements AdminTaskInCompany, ManagerTaskInCompany, SalesTaskInCompany
{

    public function __construct(protected AreaStructureRepository $areaStructureRepository)
    {
        
    }

    /**
     * 
     * @param ViewDetailPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $payload->setResult($this->areaStructureRepository->viewAreaStructureDetail($payload->id));
    }
}
