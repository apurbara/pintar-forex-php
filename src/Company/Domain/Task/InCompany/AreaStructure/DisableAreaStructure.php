<?php

namespace Company\Domain\Task\InCompany\AreaStructure;

use Company\Domain\Model\AdminTaskInCompany;

class DisableAreaStructure implements AdminTaskInCompany
{

    public function __construct(protected AreaStructureRepository $repository)
    {
        
    }

    /**
     * 
     * @param string $payload areaStructureId
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $this->repository->ofId($payload)
                ->disable();
    }
}
