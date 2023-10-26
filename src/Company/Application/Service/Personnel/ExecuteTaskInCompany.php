<?php

namespace Company\Application\Service\Personnel;

use Company\Domain\Model\PersonnelTaskInCompany;

class ExecuteTaskInCompany
{

    public function __construct(protected PersonnelRepository $personnelRepository)
    {
        
    }

    public function execute(string $personnelId, PersonnelTaskInCompany $task, $payload): void
    {
        $this->personnelRepository->ofId($personnelId)
                ->executeTaskInCompany($task, $payload);
        $this->personnelRepository->update();
    }
}
