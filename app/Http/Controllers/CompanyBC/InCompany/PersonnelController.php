<?php

namespace App\Http\Controllers\CompanyBC\InCompany;

use App\Http\Controllers\CompanyBC\CompanyUserRoleInterface;
use App\Http\Controllers\Controller;
use Company\Domain\Model\Personnel;
use Company\Domain\Model\PersonnelData;
use Company\Domain\Task\InCompany\Personnel\AddPersonnelTask;
use Company\Domain\Task\InCompany\Personnel\ViewPersonnelDetailTask;
use Company\Domain\Task\InCompany\Personnel\ViewPersonnelListTask;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrinePersonnelRepository;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;
use SharedContext\Domain\ValueObject\AccountInfoData;

#[GraphqlMapableController(entity: Personnel::class)]
class PersonnelController extends Controller
{
    protected function personnelRepository(): DoctrinePersonnelRepository
    {
        return $this->em->getRepository(Personnel::class);
    }
    
    //
    #[Mutation]
    public function addPersonnel(CompanyUserRoleInterface $user, InputRequest $input)
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
    
    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function viewPersonnelList(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $task = new ViewPersonnelListTask($this->personnelRepository());
        $payload = $this->buildViewPaginationListPayload($input);
        $user->executeTaskInCompany($task, $payload);
        
        return $payload->result;
    }
    
    #[Query]
    public function viewPersonnelDetail(CompanyUserRoleInterface $user, string $id)
    {
        $task = new ViewPersonnelDetailTask($this->personnelRepository());
        $payload = new ViewDetailPayload($id);
        $user->executeTaskInCompany($task, $payload);
        
        return $payload->result;
    }
}
