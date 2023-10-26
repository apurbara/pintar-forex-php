<?php

namespace App\Http\Controllers\UserRole;

use App\Http\Controllers\CompanyBC\CompanyUserRoleInterface;
use App\Http\Controllers\UserRole\Personnel\SalesRole;
use Company\Application\Service\Personnel\ExecuteTaskInCompany;
use Company\Domain\Model\Personnel;
use Company\Domain\Task\InCompany\TaskInCompany;
use Doctrine\ORM\EntityManager;
use function app;

class PersonnelRole implements CompanyUserRoleInterface
{

    protected EntityManager $em;

    public function __construct(protected string $personnelId)
    {
        $this->em = app(EntityManager::class);
    }

    public function executeTaskInCompany(TaskInCompany $task, $payload): void
    {
        $personnelRepository = $this->em->getRepository(Personnel::class);
        (new ExecuteTaskInCompany($personnelRepository))->execute($this->personnelId, $task, $payload);
    }

    public function authorizedAsSales(string $salesId): SalesRole
    {
        return new SalesRole($this, $this->personnelId, $salesId);
    }
}
