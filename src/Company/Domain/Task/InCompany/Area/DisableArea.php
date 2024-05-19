<?php

namespace Company\Domain\Task\InCompany\Area;

use Company\Domain\Model\AdminTaskInCompany;

class DisableArea implements AdminTaskInCompany
{

    public function __construct(protected AreaRepository $repository)
    {
        
    }

    /**
     * 
     * @param string $payload areaId
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $this->repository->ofId($payload)
                ->disable();
    }
}
