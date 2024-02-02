<?php

namespace App\Http\Controllers\UserBC\ByPersonnel;

use App\Http\Controllers\ManagerBC\ManagerRoleInterface;
use App\Http\Controllers\SalesBC\SalesRoleInterface;
use User\Domain\Task\ByPersonnel\PersonnelTask;

interface PersonnelRoleInterface
{

    public function executePersonnelTask(PersonnelTask $task, $payload): void;

    public function authorizedAsSales(string $salesId): SalesRoleInterface;

    public function authorizeAsManager(string $managerId): ManagerRoleInterface;
    
    public function getUserId(): string;
}
