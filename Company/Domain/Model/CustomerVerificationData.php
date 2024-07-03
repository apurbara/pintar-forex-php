<?php

namespace Company\Domain\Model;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;
use SharedContext\Domain\ValueObject\LabelData;

readonly class CustomerVerificationData extends AbstractEntityMutationPayload
{

    public int $weight;
    public int $position;

    public function __construct(public LabelData $labelData)
    {
        
    }

    public function setWeight(int $weight)
    {
        $this->weight = $weight;
        return $this;
    }

    public function setPosition(int $position)
    {
        $this->position = $position;
        return $this;
    }
}
