<?php

namespace Company\Domain\Model;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;
use SharedContext\Domain\ValueObject\DateIntervalData;
use SharedContext\Domain\ValueObject\LabelData;

readonly class CommonSalesMetricData extends AbstractEntityMutationPayload
{

    public LabelData $labelData;
    public int $target;
    public string $recurrenceType;
    public ?int $recurrenceCount;
    public ?string $displaySchema;
    public DateIntervalData $startEndDate;

    public function setLabelData(LabelData $labelData)
    {
        $this->labelData = $labelData;
        return $this;
    }

    public function setTarget(int $target)
    {
        $this->target = $target;
        return $this;
    }

    public function setRecurrenceType(string $recurrenceType)
    {
        $this->recurrenceType = $recurrenceType;
        return $this;
    }

    public function setRecurrenceCount(?int $recurrenceCount)
    {
        $this->recurrenceCount = $recurrenceCount;
        return $this;
    }

    public function setDisplaySchema(?string $displaySchema)
    {
        $this->displaySchema = $displaySchema;
        return $this;
    }

    public function setStartEndDate(DateIntervalData $startEndDate)
    {
        $this->startEndDate = $startEndDate;
        return $this;
    }
}
