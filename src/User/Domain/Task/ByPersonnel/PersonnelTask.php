<?php

namespace User\Domain\Task\ByPersonnel;

use User\Domain\Model\Personnel;

interface PersonnelTask
{

    public function executeByPersonnel(Personnel $personnel, $payload): void;
}
