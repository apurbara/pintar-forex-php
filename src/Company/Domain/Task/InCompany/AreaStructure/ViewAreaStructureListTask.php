<?php

namespace Company\Domain\Task\InCompany\AreaStructure;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\PersonnelTaskInCompany;
use Resources\Domain\TaskPayload\ViewPaginationListPayload;

class ViewAreaStructureListTask implements AdminTaskInCompany, PersonnelTaskInCompany
{
    public function __construct(protected AreaStructureRepository $areaStructureRepository)
    {
    }
    
    /**
     * 
     * @param ViewPaginationListPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $payload->setResult($this->areaStructureRepository->viewAreaStructureList($payload->paginationSchema));
    }
}
