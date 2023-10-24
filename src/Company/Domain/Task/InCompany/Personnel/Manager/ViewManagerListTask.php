<?php

namespace Company\Domain\Task\InCompany\Personnel\Manager;

use Company\Domain\Model\AdminTaskInCompany;
use Resources\Domain\TaskPayload\ViewPaginationListPayload;

class ViewManagerListTask implements AdminTaskInCompany
{
    
    public function __construct(protected ManagerRepository $managerRepository)
    {
    }
    
    /**
     * 
     * @param ViewPaginationListPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $payload->setResult($this->managerRepository->managerList($payload->paginationSchema));
    }
}
