<?php

namespace Company\Application\Controllers;

use App\Http\Controllers\Controller;
use Company\Domain\Model\CompanyUser;
use Company\Domain\Task\TaskInCompany;

class BaseController extends Controller
{

    protected function executeMutationTaskInCompany(CompanyUser $user, TaskInCompany $task, $payload): void
    {
        $user->executeTaskInCompany($task, $payload);
        $this->em->flush();
    }
}
