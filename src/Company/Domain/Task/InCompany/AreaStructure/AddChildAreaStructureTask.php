<?php

namespace Company\Domain\Task\InCompany\AreaStructure;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\AreaStructureData;
use Resources\Exception\RegularException;

class AddChildAreaStructureTask implements AdminTaskInCompany
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
            throw RegularException::conflict('name is unavailable');
        }
        $payload->setId($this->areaStructureRepository->nextIdentity());
        $areaStructure = $this->areaStructureRepository->ofId($payload->parentId)
                ->createChild($payload);
        $this->areaStructureRepository->add($areaStructure);
    }
}
