<?php

namespace App\Http\Controllers\CompanyBC;

use Company\Application\Service\Manager\ExecuteTaskInCompany;
use Company\Domain\Model\Manager;
use Company\Domain\Task\InCompany\TaskInCompany;
use Doctrine\ORM\EntityManager;
use function app;

class ManagerRole implements CompanyUserRoleInterface
{

    protected EntityManager $em;

    public function __construct(protected string $managerId)
    {
        $this->em = app(EntityManager::class);
    }

    public function executeTaskInCompany(TaskInCompany $task, $payload): void
    {
        $repository = $this->em->getRepository(Manager::class);
        (new ExecuteTaskInCompany($repository))
                ->execute($this->managerId, $task, $payload);
    }
}
