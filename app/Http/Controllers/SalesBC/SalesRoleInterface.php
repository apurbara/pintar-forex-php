<?php

namespace App\Http\Controllers\SalesBC;

use Sales\Domain\Task\SalesTask;

interface SalesRoleInterface
{

    public function executeTask(SalesTask $task, $payload): void;
}
