<?php

namespace Company\Domain\Model;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;
use SharedContext\Domain\ValueObject\LabelData;

readonly class CustomerJourneyData extends AbstractEntityMutationPayload
{

    public bool $initial;

    public function setInitial()
    {
        $this->initial = true;
        return $this;
    }

    public function __construct(public LabelData $labelData)
    {
        
    }
}
