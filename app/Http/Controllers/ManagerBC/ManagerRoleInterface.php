<?php

namespace App\Http\Controllers\ManagerBC;

use Manager\Domain\Task\ManagerTask;

interface ManagerRoleInterface
{

    public function executeManagerTask(ManagerTask $task, $payload): void;
}
