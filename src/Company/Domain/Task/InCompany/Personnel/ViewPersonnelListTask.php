<?php

namespace Company\Domain\Task\InCompany\Personnel;

use Company\Domain\Model\AdminTaskInCompany;
use Resources\Domain\TaskPayload\ViewPaginationListPayload;

class ViewPersonnelListTask implements AdminTaskInCompany
{
    public function __construct(protected PersonnelRepository $personnelRepository)
    {
    }
    
    /**
     * 
     * @param ViewPaginationListPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $payload->setResult($this->personnelRepository->viewPersonnelList($payload->paginationSchema));
    }
}
