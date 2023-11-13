<?php

namespace App\Http\Controllers\UserRole\Personnel;

use App\Http\Controllers\ManagerBC\ManagerRoleInterface;
use App\Http\Controllers\UserRole\PersonnelRole;
use Manager\Application\Service\Manager\ExecuteManagerTask;
use Manager\Domain\Model\Personnel\Manager;
use Manager\Domain\Task\ManagerTask;

class ManagerRole extends PersonnelRole implements ManagerRoleInterface
{

    public function __construct(string $personnelId, protected string $managerId)
    {
        parent::__construct($personnelId);
    }

    //
    public function executeManagerTask(ManagerTask $task, $payload): void
    {
        $managerRepository = $this->em->getRepository(Manager::class);
        (new ExecuteManagerTask($managerRepository))
                ->excute($this->personnelId, $this->managerId, $task, $payload);
    }
}
