<?php

namespace User\Application\Service\Guest;

use User\Domain\Model\Personnel;

interface PersonnelRepository
{

    public function ofEmail(string $email): Personnel;
}
