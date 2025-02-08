<?php

namespace Manager\Domain\Model\Manager\Sales\FactFindingAssignment;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;

readonly class ClosingRequestByFactFinderData extends AbstractEntityMutationPayload
{

    public ?string $remark;

    public function setRemark(?string $remark)
    {
        $this->remark = $remark;
        return $this;
    }
}
