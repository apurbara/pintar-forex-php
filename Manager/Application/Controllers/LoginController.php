<?php

namespace Manager\Application\Controllers;

use Manager\Domain\Model\Manager;
use Manager\Infrastructure\Persistence\Doctrine\Repository\DoctrineManagerRepository;
use Resources\Application\InputRequest;
use Shared\Application\Controllers\Controller;
use Shared\Application\JwtHelper;
use Shared\Application\UserRole;

class LoginController extends Controller
{

    private function repository(): DoctrineManagerRepository
    {
        return $this->em->getRepository(Manager::class);
    }

    public function login(InputRequest $input)
    {
        $repository = $this->repository();
        $manager = $repository->ofEmail($input->get('email'));
        $managerId = $manager->login($input->get('password'));

        $result = $repository->queryOneById($managerId);
        return [
            ...$result,
            'token' => JwtHelper::generateJwtToken(UserRole::MANAGER, $result['id']),
        ];
    }
}
