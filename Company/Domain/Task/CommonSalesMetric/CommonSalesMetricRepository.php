<?php

namespace Company\Domain\Task\CommonSalesMetric;

use Company\Domain\Model\CommonSalesMetric;

interface CommonSalesMetricRepository
{

    public function nextIdentity(): string;

    public function add(CommonSalesMetric $commonSalesMetric): void;

    public function ofId(string $id): CommonSalesMetric;

    //
    public function aCommonSalesMetric(string $id);

    public function commonSalesMetricList(array $paginationSchema);
}
