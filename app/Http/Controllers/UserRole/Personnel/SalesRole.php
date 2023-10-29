<?php

namespace App\Http\Controllers\UserRole\Personnel;

use App\Http\Controllers\SalesBC\SalesRoleInterface;
use App\Http\Controllers\UserRole\PersonnelRole;
use Sales\Application\Service\Sales\ExecuteSalesTask;
use Sales\Domain\Model\Personnel\Sales;
use Sales\Domain\Task\SalesTask;

class SalesRole extends PersonnelRole implements SalesRoleInterface
{
    public function __construct(string $personnelId, protected string $salesId)
    {
        parent::__construct($personnelId);
    }

    //
    public function executeSalesTask(SalesTask $task, $payload): void
    {
        $salesRepository = $this->em->getRepository(Sales::class);
        (new ExecuteSalesTask($salesRepository))
                ->excute($this->personnelId, $this->salesId, $task, $payload);
    }
}
