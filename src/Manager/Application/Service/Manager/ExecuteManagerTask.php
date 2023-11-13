<?php

namespace Manager\Application\Service\Manager;

use Manager\Domain\Task\ManagerTask;

class ExecuteManagerTask
{

    public function __construct(protected ManagerRepository $managerRepository)
    {
        
    }

    public function excute(string $personnelId, string $managerId, ManagerTask $task, $payload): void
    {
        $this->managerRepository->aManagerBelongsToPersonnel($personnelId, $managerId)
                ->executeTask($task, $payload);
        $this->managerRepository->update();
    }
}
