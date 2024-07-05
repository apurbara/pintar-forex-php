<?php

namespace Sales\Domain\Task;

use Sales\Domain\Model\Sales;

interface SalesTask
{

    public function executeBySales(Sales $sales, $payload): void;
}
