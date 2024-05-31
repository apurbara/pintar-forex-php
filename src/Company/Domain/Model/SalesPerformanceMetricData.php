<?php

namespace Company\Domain\Model;

use Company\Domain\Model\SalesPerformanceMetric\SalesPerformanceMetricEvaluationData;

class SalesPerformanceMetricData
{

    public readonly ?string $id;
    public readonly ?string $name;
    public readonly ?string $metricType;
    public readonly ?string $recurrenceType;
    public readonly ?int $recurrenceCount;
    public readonly ?string $displaySchema;
    //
    protected array $evaluations = [];

    public function setId(?string $id)
    {
        $this->id = $id;
        return $this;
    }

    public function setName(?string $name)
    {
        $this->name = $name;
        return $this;
    }

    public function setMetricType(?string $metricType)
    {
        $this->metricType = $metricType;
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

    //
    public function addEvaluationData(SalesPerformanceMetricEvaluationData $evaluationData): static
    {
        $this->evaluations[$evaluationData->evaluationType] = $evaluationData;
        return $this;
    }

    public function pullEvaluationDataAssociationWithType(?string $evaluationType): ?SalesPerformanceMetricEvaluationData
    {
        $evaluationData = $this->evaluations[$evaluationType] ?? null;
        if ($evaluationData) {
            unset($this->evaluations[$evaluationType]);
        }
        return $evaluationData;
    }

    /**
     * 
     * @return SalesPerformanceMetricEvaluationData[]
     */
    public function getEvaluations(): array
    {
        return $this->evaluations;
    }
}
