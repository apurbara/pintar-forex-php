<?php

namespace App\Http\Controllers\CompanyBC\InCompany;

use App\Http\Controllers\CompanyBC\CompanyUserRoleInterface;
use App\Http\Controllers\Controller;
use Company\Domain\Model\Manager;
use Company\Domain\Model\ManagerData;
use Company\Domain\Task\InCompany\Manager\AddManagerTask;
use Company\Domain\Task\InCompany\Manager\SuspendManager;
use Company\Domain\Task\InCompany\Manager\UnsuspendManager;
use Company\Domain\Task\InCompany\Manager\ViewManagerDetailTask;
use Company\Domain\Task\InCompany\Manager\ViewManagerListTask;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineManagerRepository;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;
use SharedContext\Domain\ValueObject\AccountInfoData;

#[GraphqlMapableController(entity: Manager::class)]
class ManagerController extends Controller
{

    protected function repository(): DoctrineManagerRepository
    {
        return $this->em->getRepository(Manager::class);
    }
    
    private function createData(InputRequest $input): ManagerData
    {
        $name = $input->get('name');
        $email = $input->get('email');
        $password = $input->get('password');
        $accountInfoData = new AccountInfoData($name, $email, $password);
        return new ManagerData($accountInfoData);
    }

    //
    #[Mutation]
    public function addManager(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $repository = $this->repository();

        $task = new AddManagerTask($repository);
        $payload = $this->createData($input);

        $user->executeTaskInCompany($task, $payload);
        return $repository->queryOneById($payload->id);
    }

    #[Mutation]
    public function suspendManager(CompanyUserRoleInterface $user, string $id)
    {
        $repository = $this->repository();
        $task = new SuspendManager($repository);

        $user->executeTaskInCompany($task, $id);
        return $repository->queryOneById($id);
    }

    #[Mutation]
    public function unsuspendManager(CompanyUserRoleInterface $user, string $id)
    {
        $repository = $this->repository();
        $task = new UnsuspendManager($repository);

        $user->executeTaskInCompany($task, $id);
        return $repository->queryOneById($id);
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
