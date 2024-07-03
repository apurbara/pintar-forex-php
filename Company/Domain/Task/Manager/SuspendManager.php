<?php

namespace Company\Domain\Task\Manager;

use Company\Domain\Model\AdminTaskInCompany;

class SuspendManager implements AdminTaskInCompany
{
    public function __construct(protected ManagerRepository $repository)
    {
    }
    
    /**
     * 
     * @param string $payload managerId
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $this->repository->ofId($payload)
                ->suspend();
    }
}
