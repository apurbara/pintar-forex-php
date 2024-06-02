<?php

namespace Company\Application\Service\Manager;

use Company\Domain\Model\ManagerTaskInCompany;

class ExecuteTaskInCompany
{

    public function __construct(protected ManagerRepository $repository)
    {
        
    }

    public function execute(string $managerId, ManagerTaskInCompany $task, $payload): void
    {
        $this->repository->ofId($managerId)
                ->executeTaskInCompany($task, $payload);
        $this->repository->update();
    }
}
