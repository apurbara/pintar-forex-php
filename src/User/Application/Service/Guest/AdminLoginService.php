<?php

namespace User\Application\Service\Guest;

class AdminLoginService
{

    public function __construct(protected AdminRepository $adminRepositry)
    {
        
    }

    public function execute(string $email, string $password): string
    {
        return $this->adminRepositry->ofEmail($email)
                ->login($password);
    }
}
