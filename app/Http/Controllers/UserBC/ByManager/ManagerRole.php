<?php

namespace App\Http\Controllers\UserBC\ByManager;

use Doctrine\ORM\EntityManager;
use User\Application\Service\Manager\ExecuteManagerTask;
use User\Domain\Model\Manager;
use User\Domain\Task\ByManager\ManagerTask;
use function app;

class ManagerRole
{

    protected EntityManager $em;

    public function __construct(public readonly string $managerId)
    {
        $this->em = app(EntityManager::class);
    }

    public function executeManagerTask(ManagerTask $task, $payload): void
    {
        $managerRepository = $this->em->getRepository(Manager::class);
        (new ExecuteManagerTask($managerRepository))
                ->execute($this->managerId, $task, $payload);
    }
}
