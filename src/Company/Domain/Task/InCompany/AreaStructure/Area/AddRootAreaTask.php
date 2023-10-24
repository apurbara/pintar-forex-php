<?php

namespace Company\Domain\Task\InCompany\AreaStructure\Area;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\AreaStructure\AreaData;
use Company\Domain\Task\InCompany\AreaStructure\AreaStructureRepository;
use Resources\Exception\RegularException;

class AddRootAreaTask implements AdminTaskInCompany
{

    public function __construct(
            protected AreaRepository $areaRepository, protected AreaStructureRepository $areaStructureRepository)
    {
    }

    /**
     * 
     * @param AreaData $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        if (!$this->areaRepository->isAreaRootNameAvailable($payload->labelData->name)) {
            throw RegularException::conflict('area name is unavailable');
        }
        $payload->setId($this->areaRepository->nextIdentity());
        
        $areaStructure = $this->areaStructureRepository->ofId($payload->areaStructureId);
        $areaStructure->assertActive();
        
        $area = $areaStructure->createRootArea($payload);
        $this->areaRepository->add($area);
    }
}
