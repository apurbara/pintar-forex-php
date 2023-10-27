<?php

namespace App\Http\Controllers\UserBC\ByGuest;

use App\Http\Controllers\Controller;
use App\Http\Controllers\InputRequest;
use User\Application\Service\Guest\AdminLoginService;
use User\Application\Service\Guest\PersonnelLoginService;
use User\Domain\Model\Admin;
use User\Domain\Model\Personnel;

class LoginController extends Controller
{

    public function adminLogin(InputRequest $input)
    {
        $adminRepository = $this->em->getRepository(Admin::class);
        $service = new AdminLoginService($adminRepository);

        $adminId = $service->execute($input->get('email'), $input->get('password'));
        return $adminRepository->fetchOneByIdOrDie($adminId);
    }

    public function personnelLogin(InputRequest $input)
    {
        $personnelRepository = $this->em->getRepository(Personnel::class);
        $service = new PersonnelLoginService($personnelRepository);

        $personnelId = $service->execute($input->get('email'), $input->get('password'));
        return $personnelRepository->fetchOneByIdOrDie($personnelId);
    }

}
