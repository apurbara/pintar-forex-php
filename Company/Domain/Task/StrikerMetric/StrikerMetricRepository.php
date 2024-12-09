<?php

namespace Company\Domain\Task\StrikerMetric;

use Company\Domain\Model\StrikerMetric;

interface StrikerMetricRepository
{

    public function nextIdentity(): string;

    public function add(StrikerMetric $strikerMetric): void;

    public function ofId(string $id): StrikerMetric;

    //
    public function strikerMetricList(array $paginationSchema);

    public function aStrikerMetric(string $id);
}
