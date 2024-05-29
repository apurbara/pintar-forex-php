<?php

namespace App\Http\Controllers\SalesBC\BySales;

use Sales\Domain\Task\BySales\SalesTask;

interface SalesRoleInterface
{

    public function executeSalesTask(SalesTask $task, $payload): void;

    public function getPersonnelId(): string;

    public function getSalesId(): string;
}
