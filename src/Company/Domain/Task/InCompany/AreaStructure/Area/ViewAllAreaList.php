<?php

namespace Company\Domain\Task\InCompany\AreaStructure\Area;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\PersonnelTaskInCompany;
use Resources\Domain\TaskPayload\ViewAllListPayload;

class ViewAllAreaList implements AdminTaskInCompany, PersonnelTaskInCompany
{
    public function __construct(protected AreaRepository $areaRepository)
    {
    }
    
    /**
     * 
     * @param ViewAllListPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $result = $this->areaRepository->viewAllAreaList($payload->listSchema);
        $payload->setResult($result);
    }
}
