<?php

namespace Company\Domain\Model\SalesPerformanceMetric;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;

readonly class SalesPerformanceMetricEvaluationData extends AbstractEntityMutationPayload
{

    public ?string $alias;
    public ?string $evaluationType;

    public function setAlias(?string $alias)
    {
        $this->alias = $alias;
        return $this;
    }

    public function setEvaluationType(?string $evaluationType)
    {
        $this->evaluationType = $evaluationType;
        return $this;
    }
}
