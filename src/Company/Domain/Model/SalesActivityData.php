<?php

namespace Company\Domain\Model;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;
use SharedContext\Domain\ValueObject\LabelData;

readonly class SalesActivityData extends AbstractEntityMutationPayload
{

    public function __construct(public LabelData $labelData, public int $duration)
    {
        
    }
}
