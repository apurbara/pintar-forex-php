<?php

namespace Sales\Domain\Task\Customer;

interface CustomerRepository
{

    public function nextIdentity(): string;

    public function isEmailAvailable(string $email): bool;
}
