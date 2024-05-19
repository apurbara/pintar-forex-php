<?php

namespace Company\Domain\Task\InCompany\Personnel;

use Company\Domain\Model\AdminTaskInCompany;

class SuspendPersonnel implements AdminTaskInCompany
{
    public function __construct(protected PersonnelRepository $repository)
    {
    }
    
    /**
     * 
     * @param string $payload personnelId
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $this->repository->ofId($payload)
                ->suspend();
    }
}
