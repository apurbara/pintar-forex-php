<?php

namespace User\Application\Service\Guest;

class SalesLoginService
{

    public function __construct(protected SalesRepository $salesRepositry)
    {
        
    }

    public function execute(string $email, string $password): string
    {
        return $this->salesRepositry->activeSalesByEmail($email)
                ->login($password);
    }
}
