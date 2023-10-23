<?php

namespace Company\Domain\Task\InCompany\AreaStructure\Area;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\AreaStructure\AreaData;
use Company\Domain\Task\InCompany\AreaStructure\AreaStructureRepository;
use Resources\Exception\RegularException;

class AddChildAreaTask implements AdminTaskInCompany
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
        if (!$this->areaRepository->isChildAreaNameAvailable($payload->parentAreaId, $payload->labelData->name)) {
            throw RegularException::conflict('area name is unavailable');
        }
        $payload->setId($this->areaRepository->nextIdentity());
        $childAreaStructure = $this->areaStructureRepository->ofId($payload->areaStructureId);
        $area = $this->areaRepository->ofId($payload->parentAreaId)
                ->createChild($childAreaStructure, $payload);
        $this->areaRepository->add($area);
    }
}
