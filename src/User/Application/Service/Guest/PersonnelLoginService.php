<?php

namespace User\Application\Service\Guest;

class PersonnelLoginService
{

    public function __construct(protected PersonnelRepository $personnelRepositry)
    {
        
    }

    public function execute(string $email, string $password): string
    {
        return $this->personnelRepositry->ofEmail($email)
                ->login($password);
    }
}
