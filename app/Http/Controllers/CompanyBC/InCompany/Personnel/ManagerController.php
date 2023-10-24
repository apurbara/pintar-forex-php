<?php

namespace App\Http\Controllers\CompanyBC\InCompany\Personnel;

use App\Http\Controllers\CompanyBC\CompanyUserRoleInterface;
use App\Http\Controllers\Controller;
use App\Http\Controllers\InputRequest;
use Company\Domain\Model\Personnel;
use Company\Domain\Model\Personnel\Manager;
use Company\Domain\Model\Personnel\ManagerData;
use Company\Domain\Task\InCompany\Personnel\Manager\AssignManagerTask;
use Company\Domain\Task\InCompany\Personnel\Manager\ViewManagerDetailTask;
use Company\Domain\Task\InCompany\Personnel\Manager\ViewManagerListTask;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineManagerRepository;
use Resources\Domain\TaskPayload\ViewDetailPayload;

class ManagerController extends Controller
{
    protected function repository(): DoctrineManagerRepository
    {
        return $this->em->getRepository(Manager::class);
    }
    
    //
    public function assign(CompanyUserRoleInterface $user, string $personnelId)
    {
        $repository = $this->repository();
        $personnelRepository = $this->em->getRepository(Personnel::class);
        
        $task = new AssignManagerTask($repository, $personnelRepository);
        $payload = (new ManagerData())
                ->setPersonnelId($personnelId);
        
        $user->executeTaskInCompany($task, $payload);
        //
        return $this->repository()->fetchOneByIdOrDie($payload->id);
    }
    
    public function viewList(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $task = new ViewManagerListTask($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);
        $user->executeTaskInCompany($task, $payload);
        
        return $payload->result;
    }
    
    public function viewDetail(CompanyUserRoleInterface $user, string $managerId)
    {
        $task = new ViewManagerDetailTask($this->repository());
        $payload = new ViewDetailPayload($managerId);
        $user->executeTaskInCompany($task, $payload);
        
        return $payload->result;
    }
}
