<?php

namespace App\Http\Controllers\CompanyBC\InCompany;

use App\Http\Controllers\CompanyBC\CompanyUserRoleInterface;
use App\Http\Controllers\Controller;
use App\Http\Controllers\InputRequest;
use Company\Domain\Model\Personnel;
use Company\Domain\Model\PersonnelData;
use Company\Domain\Task\InCompany\Personnel\AddPersonnelTask;
use Company\Domain\Task\InCompany\Personnel\ViewPersonnelDetailTask;
use Company\Domain\Task\InCompany\Personnel\ViewPersonnelListTask;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrinePersonnelRepository;
use SharedContext\Domain\ValueObject\AccountInfoData;

class PersonnelController extends Controller
{
    protected function personnelRepository(): DoctrinePersonnelRepository
    {
        return $this->em->getRepository(Personnel::class);
    }
    
    //
    public function add(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $task = new AddPersonnelTask($this->personnelRepository());
        $name = $input->get('name');
        $email = $input->get('email');
        $password = $input->get('password');
        $accountInfoData = new AccountInfoData($name, $email, $password);
        $payload = new PersonnelData($accountInfoData);
        $user->executeTaskInCompany($task, $payload);
        //
        return $this->personnelRepository()->fetchOneByIdOrDie($payload->id);
    }
    
    public function viewList(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $task = new ViewPersonnelListTask($this->personnelRepository());
        $payload = $this->buildViewPaginationListPayload($input);
        $user->executeTaskInCompany($task, $payload);
        
        return $payload->result;
    }
    
    public function viewDetail(CompanyUserRoleInterface $user, string $personnelId)
    {
        $task = new ViewPersonnelDetailTask($this->personnelRepository());
        $payload = new \Resources\Domain\TaskPayload\ViewDetailPayload($personnelId);
        $user->executeTaskInCompany($task, $payload);
        
        return $payload->result;
    }
}
