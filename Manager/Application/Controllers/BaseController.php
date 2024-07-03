<?php

namespace Manager\Application\Controllers;

use App\Http\Controllers\Controller;
use Manager\Domain\Model\Manager;
use Manager\Domain\Task\ManagerTask;

class BaseController extends Controller
{
    protected function executeManagerTask(Manager $manager, ManagerTask $task, $payload): void
    {
        $manager->executeTask($task, $payload);
        $this->em->flush();
    }
}
