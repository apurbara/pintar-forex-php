<?php

namespace Company\Domain\Task\InCompany\AreaStructure;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\AreaStructureData;

class UpdateAreaStructure implements AdminTaskInCompany
{

    public function __construct(protected AreaStructureRepository $repository)
    {
        
    }

    /**
     * 
     * @param AreaStructureData $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $this->repository->ofId($payload->id)
                ->update($payload);
    }
}
