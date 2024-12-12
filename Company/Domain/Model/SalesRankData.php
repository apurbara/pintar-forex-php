<?php

namespace Company\Domain\Model;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;

readonly class SalesRankData extends AbstractEntityMutationPayload
{

    public ?string $name;
    public ?string $salesRole;
    public ?string $salesMetricType;
    public ?string $evaluationType;
    public ?string $recurrenceType;
    public ?int $displaySalesNumber;
    public ?string $order;
    public ?string $displaySchema;

    public function setName(?string $name)
    {
        $this->name = $name;
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

    public function setDisplaySalesNumber(?int $displaySalesNumber)
    {
        $this->displaySalesNumber = $displaySalesNumber;
        return $this;
    }

    public function setOrder(?string $order)
    {
        $this->order = $order;
        return $this;
    }

    public function setDisplaySchema(?string $displaySchema)
    {
        $this->displaySchema = $displaySchema;
        return $this;
    }

    public function setSalesRole(?string $salesRole)
    {
        $this->salesRole = $salesRole;
        return $this;
    }
}
