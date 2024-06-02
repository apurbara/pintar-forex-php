<?php

namespace User\Application\Service\Guest;

use User\Domain\Model\Manager;

interface ManagerRepository
{
    public function ofEmail(string $email): Manager;
}
