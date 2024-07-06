<?php

namespace Company\Application\Controllers;

use Company\Domain\Model\CompanyUser;
use Company\Domain\Model\Manager;
use Company\Domain\Model\ManagerData;
use Company\Domain\Task\Manager\AddManagerTask;
use Company\Domain\Task\Manager\SuspendManager;
use Company\Domain\Task\Manager\UnsuspendManager;
use Company\Domain\Task\Manager\ViewManagerDetailTask;
use Company\Domain\Task\Manager\ViewManagerListTask;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineManagerRepository;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;
use Shared\Domain\ValueObject\AccountInfoData;

#[GraphqlMapableController(entity: Manager::class)]
class ManagerController extends BaseController
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
    public function addManager(CompanyUser $user, InputRequest $input)
    {
        $repository = $this->repository();

        $task = new AddManagerTask($repository);
        $payload = $this->createData($input);

        $this->executeMutationTaskInCompany($user, $task, $payload);
        return $repository->queryOneById($payload->id);
    }

    #[Mutation]
    public function suspendManager(CompanyUser $user, string $id)
    {
        $repository = $this->repository();
        $task = new SuspendManager($repository);

        $this->executeMutationTaskInCompany($user, $task, $id);
        return $repository->queryOneById($id);
    }

    #[Mutation]
    public function unsuspendManager(CompanyUser $user, string $id)
    {
        $repository = $this->repository();
        $task = new UnsuspendManager($repository);

        $this->executeMutationTaskInCompany($user, $task, $id);
        return $repository->queryOneById($id);
    }

    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function viewManagerList(CompanyUser $user, InputRequest $input)
    {
        $task = new ViewManagerListTask($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }

    #[Query]
    public function viewManagerDetail(CompanyUser $user, string $id)
    {
        $task = new ViewManagerDetailTask($this->repository());
        $payload = new ViewDetailPayload($id);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }
}
