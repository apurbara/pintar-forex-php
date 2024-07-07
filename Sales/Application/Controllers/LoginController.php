<?php

namespace Sales\Application\Controllers;

use Resources\Application\InputRequest;
use Sales\Domain\Model\Sales;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesRepository;
use Shared\Application\Controllers\Controller;
use Shared\Application\JwtHelper;
use Shared\Application\UserRole;

class LoginController extends Controller
{

    private function repository(): DoctrineSalesRepository
    {
        return $this->em->getRepository(Sales::class);
    }

    public function login(InputRequest $input)
    {
        $repository = $this->repository();
        $sales = $repository->ofEmail($input->get('email'));
        $salesId = $sales->login($input->get('password'));

        $result = $repository->queryOneById($salesId);
        return [
            ...$result,
            'token' => JwtHelper::generateJwtToken(UserRole::SALES, $result['id']),
        ];
    }
}
