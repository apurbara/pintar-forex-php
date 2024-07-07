<?php

namespace Manager\Application\Controllers;

use Manager\Domain\Model\Manager;
use Manager\Domain\Task\ManagerTask;
use Shared\Application\Controllers\Controller;

class BaseController extends Controller
{
    protected function executeManagerTask(Manager $manager, ManagerTask $task, $payload): void
    {
        $manager->executeTask($task, $payload);
        $this->em->flush();
    }
}
