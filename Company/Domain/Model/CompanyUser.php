<?php

namespace Company\Domain\Model;

use Company\Domain\Task\TaskInCompany;

interface CompanyUser
{

    public function executeTaskInCompany(TaskInCompany $task, $payload): void;
}
