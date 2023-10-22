<?php

namespace App\Http\Controllers\CompanyBC;

use Company\Domain\Task\InCompany\TaskInCompany;

interface CompanyUserRoleInterface
{

    public function executeTaskInCompany(TaskInCompany $task, $payload): void;
}
