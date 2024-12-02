<?php

namespace Company\Domain\Task\GreeterMetric;

use Company\Domain\Model\GreeterMetric;

interface GreeterMetricRepository
{

    public function nextIdentity(): string;

    public function add(GreeterMetric $greeterMetric): void;

    public function ofId(string $id): GreeterMetric;

    //
    public function greeterMetricList(array $paginationSchema);

    public function aGreeterMetric(string $id);
}
