<?php

namespace App\Http\Controllers\CompanyBC\InCompany\Personnel;

use App\Http\Controllers\CompanyBC\CompanyUserRoleInterface;
use App\Http\Controllers\Controller;
use Company\Domain\Model\Personnel;
use Company\Domain\Model\Personnel\Manager;
use Company\Domain\Model\Personnel\ManagerData;
use Company\Domain\Task\InCompany\Personnel\Manager\AssignManagerTask;
use Company\Domain\Task\InCompany\Personnel\Manager\ViewManagerDetailTask;
use Company\Domain\Task\InCompany\Personnel\Manager\ViewManagerListTask;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineManagerRepository;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;

#[GraphqlMapableController(entity: Manager::class)]
class ManagerController extends Controller
{
    protected function repository(): DoctrineManagerRepository
    {
        return $this->em->getRepository(Manager::class);
    }
    
    //
    #[Mutation]
    public function assignManager(CompanyUserRoleInterface $user, ?string $personnelId, InputRequest $input)
    {
        $repository = $this->repository();
        $personnelRepository = $this->em->getRepository(Personnel::class);
        
        $task = new AssignManagerTask($repository, $personnelRepository);
        $payload = (new ManagerData())
                ->setPersonnelId($personnelId ?? $input->get('Personnel_id'));
        
        $user->executeTaskInCompany($task, $payload);
        //
        return $this->repository()->fetchOneByIdOrDie($payload->id);
    }
    
    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function viewManagerList(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $task = new ViewManagerListTask($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);
        $user->executeTaskInCompany($task, $payload);
        
        return $payload->result;
    }
    
    #[Query]
    public function viewManagerDetail(CompanyUserRoleInterface $user, string $id)
    {
        $task = new ViewManagerDetailTask($this->repository());
        $payload = new ViewDetailPayload($id);
        $user->executeTaskInCompany($task, $payload);
        
        return $payload->result;
    }
}
