<?php

namespace Company\Domain\Model;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;

readonly class CompanyMetricData extends AbstractEntityMutationPayload
{

    public ?string $name;
    public ?int $target;
    public ?string $metricType;
    public ?string $evaluationType;
    public ?string $recurrenceType;
    public ?int $recurrenceCount;
    public ?string $displaySchema;

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

    public function setMetricType(?string $metricType)
    {
        $this->metricType = $metricType;
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

    public function setDisplaySchema(?string $displaySchema)
    {
        $this->displaySchema = $displaySchema;
        return $this;
    }
}
