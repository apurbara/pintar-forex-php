<?php

namespace User\Domain\Task\BySales;

use SharedContext\Domain\ValueObject\ChangeUserPasswordData;
use User\Domain\Model\Sales;
use User\Domain\Task\BySales\SalesTask;

class ChangePassword implements SalesTask
{

    /**
     * 
     * @param Sales $sales
     * @param ChangeUserPasswordData $payload
     * @return void
     */
    public function executeBySales(Sales $sales, $payload): void
    {
        $sales->changePassword($payload);
    }
}
