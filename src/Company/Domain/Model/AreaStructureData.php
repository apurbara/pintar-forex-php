<?php

namespace Company\Domain\Model;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;
use SharedContext\Domain\ValueObject\LabelData;

readonly class AreaStructureData extends AbstractEntityMutationPayload
{

    public string $parentId;

    public function __construct(public LabelData $labelData)
    {
        
    }

    public function setParentId(string $parentId): static
    {
        $this->parentId = $parentId;
        return $this;
    }


}
