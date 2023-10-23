<?php

namespace Company\Domain\Model\AreaStructure;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;
use SharedContext\Domain\ValueObject\LabelData;

readonly class AreaData extends AbstractEntityMutationPayload
{

    public string $parentAreaId;
    public string $areaStructureId;

    public function __construct(public LabelData $labelData)
    {
        
    }

    public function setAreaStructureId(string $areaStructureId): static
    {
        $this->areaStructureId = $areaStructureId;
        return $this;
    }

    public function setParentAreaId(string $parentAreaId): static
    {
        $this->parentAreaId = $parentAreaId;
        return $this;
    }
}
