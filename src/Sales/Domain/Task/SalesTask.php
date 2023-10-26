<?php

namespace Sales\Domain\Task;

use Sales\Domain\Model\Personnel\Sales;

interface SalesTask
{

    public function executeBySales(Sales $sales, $payload): void;
}
