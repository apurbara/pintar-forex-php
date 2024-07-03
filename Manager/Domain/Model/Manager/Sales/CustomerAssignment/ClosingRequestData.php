<?php

namespace Manager\Domain\Model\Manager\Sales\CustomerAssignment;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;

readonly class ClosingRequestData extends AbstractEntityMutationPayload
{

    public ?string $remark;

    public function setRemark(?string $remark)
    {
        $this->remark = $remark;
        return $this;
    }
}
