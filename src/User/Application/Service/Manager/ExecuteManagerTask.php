<?php

namespace User\Application\Service\Manager;

use User\Domain\Task\ByManager\ManagerTask;

class ExecuteManagerTask
{

    public function __construct(protected ManagerRepository $managerRepository)
    {
        
    }

    public function execute(string $managerId, ManagerTask $task, $payload): void
    {
        $this->managerRepository->ofId($managerId)->executeTask($task, $payload);
        $this->managerRepository->update();
    }
}
