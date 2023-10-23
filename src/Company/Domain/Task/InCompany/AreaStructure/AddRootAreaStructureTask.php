<?php

namespace Company\Domain\Task\InCompany\AreaStructure;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\AreaStructure;
use Company\Domain\Model\AreaStructureData;

class AddRootAreaStructureTask implements AdminTaskInCompany
{

    public function __construct(protected AreaStructureRepository $areaStructureRepository)
    {
        
    }

    /**
     * 
     * @param AreaStructureData $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        if (!$this->areaStructureRepository->isNameAvailable($payload->labelData->name)) {
            throw \Resources\Exception\RegularException::conflict('name is not available');
        }
        $payload->setId($this->areaStructureRepository->nextIdentity());
        $areaStructure = new AreaStructure($payload);
        $this->areaStructureRepository->add($areaStructure);
    }
}
