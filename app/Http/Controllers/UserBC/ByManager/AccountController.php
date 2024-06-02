<?php

namespace App\Http\Controllers\UserBC\ByManager;

use App\Http\Controllers\Controller;
use Resources\Application\InputRequest;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;
use SharedContext\Domain\ValueObject\ChangeUserPasswordData;
use User\Domain\Model\Manager;
use User\Domain\Task\ByManager\ChangeName;
use User\Domain\Task\ByManager\ChangePassword;
use User\Infrastructure\Persistence\Doctrine\Repository\DoctrineManagerRepository;

#[GraphqlMapableController(entity: Manager::class)]
class AccountController extends Controller
{

    protected function repository(): DoctrineManagerRepository
    {
        return $this->em->getRepository(Manager::class);
    }

    //
    #[Mutation]
    public function changeName(ManagerRole $user, InputRequest $input)
    {
        $task = new ChangeName();
        $payload = $input->get('name');
        $user->executeManagerTask($task, $payload);

        return $this->repository()->queryOneById($user->managerId);
    }

    public function changePassword(ManagerRole $user, InputRequest $input)
    {
        $task = new ChangePassword();
        $payload = new ChangeUserPasswordData($input->get('previousPassword'), $input->get('newPassword'));
        $user->executeManagerTask($task, $payload);
    }

    //
    #[Query]
    public function viewProfile(ManagerRole $user)
    {
        return $this->repository()->queryOneById($user->managerId);
    }
}
