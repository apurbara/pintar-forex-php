<?php

namespace Company\Domain\Model\Personnel\Sales\CustomerAssignment;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;

readonly class RecycleRequestData extends AbstractEntityMutationPayload
{

    public ?string $remark;

    public function setRemark(?string $remark)
    {
        $this->remark = $remark;
        return $this;
    }
}
