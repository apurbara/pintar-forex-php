<?php

namespace User\Application\Service\Personnel;

use User\Domain\Task\ByPersonnel\PersonnelTask;

class ExecutePersonnelTask
{

    public function __construct(protected PersonnelRepository $personnelRepository)
    {
        
    }

    public function execute(string $personnelId, PersonnelTask $task, $payload): void
    {
        $this->personnelRepository->ofId($personnelId)->executeTask($task, $payload);
        $this->personnelRepository->update();
    }
}
