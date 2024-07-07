<?php

namespace Sales\Application\Controllers;

use Resources\Application\InputRequest;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Sales\Domain\Model\Sales;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesRepository;
use Shared\Application\Controllers\Controller;
use Shared\Domain\ValueObject\ChangeUserPasswordData;

#[GraphqlMapableController(entity: Sales::class)]
class AccountController extends Controller
{

    private function repository(): DoctrineSalesRepository
    {
        return $this->em->getRepository(Sales::class);
    }

    #[Mutation]
    public function editAccount(Sales $sales, InputRequest $input)
    {
        $sales->changeName($input->get('name'));
        $this->em->flush();

        return $this->repository()->queryOneById($sales->getId());
    }

    public function changePassword(Sales $sales, InputRequest $input)
    {
        $changePasswordData = new ChangeUserPasswordData($input->get('previousPassword'), $input->get('newPassword'));
        $sales->changePassword($changePasswordData);
        $this->em->flush();
    }
}
