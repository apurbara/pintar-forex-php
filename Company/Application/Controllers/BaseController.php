<?php

namespace Company\Application\Controllers;

use Company\Domain\Model\CompanyUser;
use Company\Domain\Task\TaskInCompany;
use Shared\Application\Controllers\Controller;

class BaseController extends Controller
{

    protected function executeMutationTaskInCompany(CompanyUser $user, TaskInCompany $task, $payload): void
    {
        $user->executeTaskInCompany($task, $payload);
        $this->em->flush();
    }
}
