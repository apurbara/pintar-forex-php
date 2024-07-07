<?php

namespace Admin\Application\Controllers;

use Admin\Domain\Model\Admin;
use Admin\Infrastructure\Persistence\Doctrine\Repository\DoctrineAdminRepository;
use Resources\Application\InputRequest;
use Shared\Application\Controllers\Controller;
use Shared\Application\JwtHelper;
use Shared\Application\UserRole;

class LoginController extends Controller
{

    private function repository(): DoctrineAdminRepository
    {
        return $this->em->getRepository(Admin::class);
    }

    public function login(InputRequest $input)
    {
        $repository = $this->repository();
        $admin = $repository->ofEmail($input->get('email'));
        $adminId = $admin->login($input->get('password'));

        $result = $repository->queryOneById($adminId);
        return [
            ...$result,
            'token' => JwtHelper::generateJwtToken(UserRole::ADMIN, $result['id']),
        ];
    }
}
