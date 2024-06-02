<?php

namespace User\Application\Service\Guest;

class ManagerLoginService
{

    public function __construct(protected ManagerRepository $managerRepositry)
    {
        
    }

    public function execute(string $email, string $password): string
    {
        return $this->managerRepositry->ofEmail($email)
                ->login($password);
    }
}
