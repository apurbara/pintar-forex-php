<?php

namespace User\Domain\Task\BySales;

use User\Domain\Model\Sales;
use User\Domain\Task\BySales\SalesTask;

class ChangeName implements SalesTask
{

    /**
     * 
     * @param Sales $sales
     * @param string $payload new name
     * @return void
     */
    public function executeBySales(Sales $sales, $payload): void
    {
        $sales->changeName($payload);
    }
}
