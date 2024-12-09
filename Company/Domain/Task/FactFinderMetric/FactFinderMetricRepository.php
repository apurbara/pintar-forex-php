<?php

namespace Company\Domain\Task\FactFinderMetric;

use Company\Domain\Model\FactFinderMetric;

interface FactFinderMetricRepository
{

    public function nextIdentity(): string;

    public function add(FactFinderMetric $factFinderMetric): void;

    public function ofId(string $id): FactFinderMetric;

    //
    public function factFinderMetricList(array $paginationSchema);

    public function aFactFinderMetric(string $id);
}
