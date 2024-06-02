<?php

namespace App\Http\Controllers\CompanyBC;

use Company\Application\Service\Admin\ExecuteTaskInCompany;
use Company\Domain\Model\Admin;
use Company\Domain\Task\InCompany\TaskInCompany;
use Doctrine\ORM\EntityManager;
use function app;

class AdminRole implements CompanyUserRoleInterface
{

    protected EntityManager $em;

    public function __construct(protected string $adminId)
    {
        $this->em = app(EntityManager::class);
    }

    public function executeTaskInCompany(TaskInCompany $task, $payload): void
    {
        $adminRepository = $this->em->getRepository(Admin::class);
        (new ExecuteTaskInCompany($adminRepository))
                ->execute($this->adminId, $task, $payload);
    }
}
