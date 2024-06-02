<?php

namespace User\Domain\Task\BySales;

use User\Domain\Model\Sales;

interface SalesTask
{

    public function executeBySales(Sales $sales, $payload): void;
}
