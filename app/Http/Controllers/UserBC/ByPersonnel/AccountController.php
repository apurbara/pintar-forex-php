<?php

namespace App\Http\Controllers\UserBC\ByPersonnel;

use App\Http\Controllers\Controller;
use Resources\Application\InputRequest;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;
use SharedContext\Domain\ValueObject\ChangeUserPasswordData;
use User\Domain\Model\Personnel;
use User\Domain\Task\ByPersonnel\ChangeName;
use User\Domain\Task\ByPersonnel\ChangePassword;
use User\Infrastructure\Persistence\Doctrine\Repository\DoctrinePersonnelRepository;

#[GraphqlMapableController(entity: Personnel::class)]
class AccountController extends Controller
{

    protected function repository(): DoctrinePersonnelRepository
    {
        return $this->em->getRepository(Personnel::class);
    }

    //
    #[Mutation]
    public function changeName(PersonnelRoleInterface $user, InputRequest $input)
    {
        $task = new ChangeName();
        $payload = $input->get('name');
        $user->executePersonnelTask($task, $payload);

        return $this->repository()->queryOneById($user->getUserId());
    }

    public function changePassword(PersonnelRoleInterface $user, InputRequest $input)
    {
        $task = new ChangePassword();
        $payload = new ChangeUserPasswordData($input->get('previousPassword'), $input->get('newPassword'));
        $user->executePersonnelTask($task, $payload);
    }

    //
    #[Query]
    public function viewProfile(PersonnelRoleInterface $user)
    {
        return $this->repository()->queryOneById($user->getUserId());
    }
}
