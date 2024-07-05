<?php

namespace Sales\Domain\Task\Dependency;

interface CustomerRepository
{

    public function nextIdentity(): string;

    public function isEmailAvailable(string $email): bool;
    
    public function isPhoneAvailable(string $phone): bool;
}
