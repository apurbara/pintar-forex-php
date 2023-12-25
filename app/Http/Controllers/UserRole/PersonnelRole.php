<?php

namespace App\Http\Controllers\UserRole;

use App\Http\Controllers\CompanyBC\CompanyUserRoleInterface;
use App\Http\Controllers\ManagerBC\ManagerRoleInterface;
use App\Http\Controllers\SalesBC\SalesRoleInterface;
use App\Http\Controllers\UserBC\ByPersonnel\PersonnelRoleInterface;
use App\Http\Controllers\UserRole\Personnel\ManagerRole;
use App\Http\Controllers\UserRole\Personnel\SalesRole;
use Company\Application\Service\Personnel\ExecuteTaskInCompany;
use Company\Domain\Model\Personnel;
use Company\Domain\Task\InCompany\TaskInCompany;
use Doctrine\ORM\EntityManager;
use User\Application\Service\Personnel\ExecutePersonnelTask;
use User\Domain\Model\Personnel as PersonnelInCompanyBC;
use User\Domain\Task\ByPersonnel\PersonnelTask;
use function app;

class PersonnelRole implements CompanyUserRoleInterface, PersonnelRoleInterface
{

    protected EntityManager $em;

    public function __construct(protected string $personnelId)
    {
        $this->em = app(EntityManager::class);
    }

    //
    public function authorizedAsSales(string $salesId): SalesRoleInterface
    {
        return new SalesRole($this->personnelId, $salesId);
    }
    
    public function authorizeAsManager(string $managerId): ManagerRoleInterface
    {
        return new ManagerRole($this->personnelId, $managerId);
    }

    //
    public function executeTaskInCompany(TaskInCompany $task, $payload): void
    {
        $personnelRepository = $this->em->getRepository(Personnel::class);
        (new ExecuteTaskInCompany($personnelRepository))->execute($this->personnelId, $task, $payload);
    }

    public function executePersonnelTask(PersonnelTask $task, $payload): void
    {
        $personnelRepository = $this->em->getRepository(PersonnelInCompanyBC::class);
        (new ExecutePersonnelTask($personnelRepository))
                ->execute($this->personnelId, $task, $payload);
    }

}
