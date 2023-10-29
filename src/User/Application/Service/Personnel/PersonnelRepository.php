<?php

namespace User\Application\Service\Personnel;

use User\Domain\Model\Personnel;

interface PersonnelRepository
{
    public function ofId(string $id): Personnel;
}
