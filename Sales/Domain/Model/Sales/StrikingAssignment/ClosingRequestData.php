<?php

namespace Sales\Domain\Model\Sales\StrikingAssignment;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;

readonly class ClosingRequestData extends AbstractEntityMutationPayload
{

    public string $strikingAssignmentId;

    public function setStrikingAssignmentId(string $strikingAssignmentId)
    {
        $this->strikingAssignmentId = $strikingAssignmentId;
        return $this;
    }

    public function __construct(public ?int $transactionValue, public ?string $note)
    {
        
    }
}
