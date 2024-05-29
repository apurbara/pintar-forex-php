<?php

namespace Sales\Domain\Task\BySales;

use Sales\Domain\Model\Sales;

interface SalesTask
{

    public function executeBySales(Sales $sales, $payload): void;
}
