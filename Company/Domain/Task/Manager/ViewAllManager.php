<?php

namespace Company\Domain\Task\Manager;

use Company\Domain\Model\AdminTaskInCompany;
use Resources\Domain\TaskPayload\ViewAllListPayload;

class ViewAllManager implements AdminTaskInCompany
{
    
    public function __construct(protected ManagerRepository $repository)
    {
    }
    
    /**
     * 
     * @param ViewAllListPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $result = $this->repository->viewAllManager($payload->listSchema);
        $payload->setResult($result);
    }
}
