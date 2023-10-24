<?php

namespace Company\Domain\Model;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;
use SharedContext\Domain\ValueObject\LabelData;

readonly class CustomerVerificationData extends AbstractEntityMutationPayload
{

    public function __construct(public LabelData $labelData)
    {
        
    }
}
