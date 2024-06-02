<?php

namespace App\Http\Controllers\CompanyBC;

use Company\Application\Service\Sales\ExecuteTaskInCompany;
use Company\Domain\Model\Sales;
use Company\Domain\Task\InCompany\TaskInCompany;
use Doctrine\ORM\EntityManager;
use function app;

class SalesRole implements CompanyUserRoleInterface
{

    protected EntityManager $em;

    public function __construct(protected string $salesId)
    {
        $this->em = app(EntityManager::class);
    }

    public function executeTaskInCompany(TaskInCompany $task, $payload): void
    {
        $repository = $this->em->getRepository(Sales::class);
        (new ExecuteTaskInCompany($repository))
                ->execute($this->salesId, $task, $payload);
    }
}
