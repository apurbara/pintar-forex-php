<?php

namespace App\Http\Controllers\UserBC\ByPersonnel;

use User\Domain\Task\ByPersonnel\PersonnelTask;

interface PersonnelRoleInterface
{
    public function executePersonnelTask(PersonnelTask $task, $payload): void;
}
