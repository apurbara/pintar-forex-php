<?php

namespace Manager\Application\Controllers;

use Manager\Domain\Model\Manager;
use Manager\Infrastructure\Persistence\Doctrine\Repository\DoctrineManagerRepository;
use Resources\Application\InputRequest;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Shared\Application\Controllers\Controller;
use Shared\Domain\ValueObject\ChangeUserPasswordData;

#[GraphqlMapableController(entity: Manager::class)]
class AccountController extends Controller
{

    private function repository(): DoctrineManagerRepository
    {
        return $this->em->getRepository(Manager::class);
    }

    #[Mutation]
    public function editAccount(Manager $manager, InputRequest $input)
    {
        $manager->changeName($input->get('name'));
        $this->em->flush();

        return $this->repository()->queryOneById($manager->getId());
    }

    public function changePassword(Manager $manager, InputRequest $input)
    {
        $changePasswordData = new ChangeUserPasswordData($input->get('previousPassword'), $input->get('newPassword'));
        $manager->changePassword($changePasswordData);
        $this->em->flush();
    }
}
