<?php

namespace User\Application\Service\Guest;

use User\Domain\Model\Admin;

interface AdminRepository
{
    public function ofEmail(string $email): Admin;
}
