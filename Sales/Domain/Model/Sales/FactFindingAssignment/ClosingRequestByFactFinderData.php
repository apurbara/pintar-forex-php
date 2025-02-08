<?php

namespace Sales\Domain\Model\Sales\FactFindingAssignment;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;

readonly class ClosingRequestByFactFinderData extends AbstractEntityMutationPayload
{

    public string $factFindingAssignmentId;

    public function setFactFindingAssignmentId(string $factFindingAssignmentId)
    {
        $this->factFindingAssignmentId = $factFindingAssignmentId;
        return $this;
    }

    public function __construct(public ?int $transactionValue, public ?string $note)
    {
        
    }
}
