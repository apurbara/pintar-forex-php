<?php

namespace Company\Application\Service\Personnel;

use Company\Domain\Model\Personnel;

interface PersonnelRepository
{

    public function ofId(string $id): Personnel;

    public function update(): void;
}
