<?php

namespace App\Http\Controllers\UserBC\BySales;

use App\Http\Controllers\Controller;
use Resources\Application\InputRequest;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;
use SharedContext\Domain\ValueObject\ChangeUserPasswordData;
use User\Domain\Model\Sales;
use User\Domain\Task\BySales\ChangeName;
use User\Domain\Task\BySales\ChangePassword;
use User\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesRepository;

#[GraphqlMapableController(entity: Sales::class)]
class AccountController extends Controller
{

    protected function repository(): DoctrineSalesRepository
    {
        return $this->em->getRepository(Sales::class);
    }

    //
    #[Mutation]
    public function changeName(SalesRole $user, InputRequest $input)
    {
        $task = new ChangeName();
        $payload = $input->get('name');
        $user->executeSalesTask($task, $payload);

        return $this->repository()->queryOneById($user->salesId);
    }

    public function changePassword(SalesRole $user, InputRequest $input)
    {
        $task = new ChangePassword();
        $payload = new ChangeUserPasswordData($input->get('previousPassword'), $input->get('newPassword'));
        $user->executeSalesTask($task, $payload);
    }

    //
    #[Query]
    public function viewProfile(SalesRole $user)
    {
        return $this->repository()->queryOneById($user->salesId);
    }
}
