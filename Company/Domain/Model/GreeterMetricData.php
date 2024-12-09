<?php

namespace Company\Domain\Model;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;

readonly class GreeterMetricData extends AbstractEntityMutationPayload
{

    public ?string $name;
    public ?int $target;
    public ?int $dailyReminderTarget;
    public ?string $salesMetricType;
    public ?string $evaluationType;
    public ?string $recurrenceType;
    public ?int $recurrenceCount;

    public function setName(?string $name)
    {
        $this->name = $name;
        return $this;
    }

    public function setTarget(?int $target)
    {
        $this->target = $target;
        return $this;
    }

    public function setDailyReminderTarget(?int $dailyReminderTarget)
    {
        $this->dailyReminderTarget = $dailyReminderTarget;
        return $this;
    }

    public function setSalesMetricType(?string $salesMetricType)
    {
        $this->salesMetricType = $salesMetricType;
        return $this;
    }

    public function setEvaluationType(?string $evaluationType)
    {
        $this->evaluationType = $evaluationType;
        return $this;
    }

    public function setRecurrenceType(?string $recurrenceType)
    {
        $this->recurrenceType = $recurrenceType;
        return $this;
    }

    public function setRecurrenceCount(?int $recurrenceCount)
    {
        $this->recurrenceCount = $recurrenceCount;
        return $this;
    }

    public function __construct()
    {
        
    }
}
