<?php

namespace Admin\Application\Controllers;

use Admin\Domain\Model\Admin;
use Admin\Infrastructure\Persistence\Doctrine\Repository\DoctrineAdminRepository;
use Resources\Application\InputRequest;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Shared\Application\Controllers\Controller;
use Shared\Domain\ValueObject\ChangeUserPasswordData;

#[GraphqlMapableController(entity: Admin::class)]
class AccountController extends Controller
{

    private function repository(): DoctrineAdminRepository
    {
        return $this->em->getRepository(Admin::class);
    }

    #[Mutation]
    public function editAccount(Admin $admin, InputRequest $input)
    {
        $admin->changeName($input->get('name'));
        $this->em->flush();

        return $this->repository()->queryOneById($admin->getId());
    }

    public function changePassword(Admin $admin, InputRequest $input)
    {
        $changePasswordData = new ChangeUserPasswordData($input->get('previousPassword'), $input->get('newPassword'));
        $admin->changePassword($changePasswordData);
        $this->em->flush();
    }
}
