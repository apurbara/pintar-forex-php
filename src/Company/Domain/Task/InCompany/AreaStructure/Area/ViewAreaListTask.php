<?php

namespace Company\Domain\Task\InCompany\AreaStructure\Area;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\PersonnelTaskInCompany;
use Resources\Domain\TaskPayload\ViewPaginationListPayload;

class ViewAreaListTask implements AdminTaskInCompany, PersonnelTaskInCompany
{

    public function __construct(protected AreaRepository $areaRepository)
    {
        
    }

    /**
     * 
     * @param ViewPaginationListPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $payload->setResult($this->areaRepository->viewAreaList($payload->paginationSchema));
    }
}
