<?php

namespace App\Http\Controllers\SalesBC;

use Sales\Domain\Task\SalesTask;

interface SalesRoleInterface
{

    public function executeSalesTask(SalesTask $task, $payload): void;

    public function getPersonnelId(): string;

    public function getSalesId(): string;
}
