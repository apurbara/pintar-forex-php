<?php

namespace App\Http\Controllers\UserBC\ByGuest;

use App\Http\Controllers\Controller;
use App\Http\Controllers\JwtHelper;
use App\Http\Controllers\UserRole;
use Resources\Application\InputRequest;
use User\Application\Service\Guest\AdminLoginService;
use User\Application\Service\Guest\ManagerLoginService;
use User\Application\Service\Guest\SalesLoginService;
use User\Domain\Model\Admin;
use User\Domain\Model\Manager;
use User\Domain\Model\Sales;

class LoginController extends Controller
{

    public function adminLogin(InputRequest $input)
    {
        $adminRepository = $this->em->getRepository(Admin::class);
        $service = new AdminLoginService($adminRepository);

        $adminId = $service->execute($input->get('email'), $input->get('password'));
        return [
            ...$adminRepository->queryOneById($adminId),
            'token' => JwtHelper::generateJwtToken(UserRole::ADMIN, $adminId),
        ];
    }

    public function managerLogin(InputRequest $input)
    {
        $managerRepository = $this->em->getRepository(Manager::class);
        $service = new ManagerLoginService($managerRepository);

        $managerId = $service->execute($input->get('email'), $input->get('password'));
        return [
            ...$managerRepository->queryOneById($managerId),
            'token' => JwtHelper::generateJwtToken(UserRole::MANAGER, $managerId),
        ];
    }

    public function salesLogin(InputRequest $input)
    {
        $salesRepository = $this->em->getRepository(Sales::class);
        $service = new SalesLoginService($salesRepository);

        $salesId = $service->execute($input->get('email'), $input->get('password'));
        return [
            ...$salesRepository->queryOneById($salesId),
            'token' => JwtHelper::generateJwtToken(UserRole::SALES, $salesId),
        ];
    }

}
