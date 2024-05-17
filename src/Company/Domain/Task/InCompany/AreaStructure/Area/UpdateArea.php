<?php

namespace Company\Domain\Task\InCompany\AreaStructure\Area;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\AreaStructure\AreaData;

class UpdateArea implements AdminTaskInCompany
{
    public function __construct(protected AreaRepository $repository)
    {
    }
    
    /**
     * 
     * @param AreaData $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $this->repository->ofId($payload->id)
                ->update($payload);
    }
}
