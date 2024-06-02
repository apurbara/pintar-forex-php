<?php

namespace User\Application\Service\Guest;

use User\Domain\Model\Sales;

interface SalesRepository
{
    public function activeSalesByEmail(string $email): Sales;
}
