<?php

namespace App\Http\Controllers\UserRole\Personnel;

use App\Http\Controllers\CompanyBC\CompanyUserRoleInterface;
use App\Http\Controllers\SalesBC\SalesRoleInterface;
use App\Http\Controllers\UserRole\PersonnelRole;
use Company\Domain\Task\InCompany\TaskInCompany;
use Doctrine\ORM\EntityManager;
use Sales\Application\Service\Sales\ExecuteSalesTask;
use Sales\Domain\Model\Personnel\Sales;
use Sales\Domain\Task\SalesTask;
use function app;

class SalesRole implements SalesRoleInterface, CompanyUserRoleInterface
{

    protected EntityManager $em;

    public function __construct(protected PersonnelRole $personnelRole, protected string $personnelId,
            protected string $salesId)
    {
        $this->em = app(EntityManager::class);
    }

    public function executeTask(SalesTask $task, $payload): void
    {
        $salesRepository = $this->em->getRepository(Sales::class);
        (new ExecuteSalesTask($salesRepository))
                ->excute($this->personnelId, $this->salesId, $task, $payload);
    }

    public function executeTaskInCompany(TaskInCompany $task, $payload): void
    {
        $this->personnelRole->executeTaskInCompany($task, $payload);
    }
}
