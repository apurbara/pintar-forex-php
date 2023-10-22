<?php

namespace Company\Application\Service\Admin;

use Company\Domain\Model\Admin;

interface AdminRepository
{

    public function ofId(string $id): Admin;

    public function update(): void;
}
