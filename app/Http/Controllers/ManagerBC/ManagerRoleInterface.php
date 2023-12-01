<?php

namespace App\Http\Controllers\ManagerBC;

use Manager\Application\Service\Manager\ExecuteManagerTask;
use Manager\Domain\Task\ManagerTask;

interface ManagerRoleInterface
{

    public function executeManagerTask(ManagerTask $task, $payload): void;

    public function getPersonnelId(): string;

    public function getManagerId(): string;

    public function buildExecuteManagerTaskService(): ExecuteManagerTask;
}
