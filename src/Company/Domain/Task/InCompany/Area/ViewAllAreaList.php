<?php

namespace Company\Domain\Task\InCompany\Area;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\ManagerTaskInCompany;
use Company\Domain\Model\SalesTaskInCompany;
use Resources\Domain\TaskPayload\ViewAllListPayload;

class ViewAllAreaList implements AdminTaskInCompany, ManagerTaskInCompany, SalesTaskInCompany
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
