<?php

namespace Company\Domain\Task\Admin;

use Company\Domain\Model\Admin;

interface AdminRepository
{

    public function ofId(string $id): Admin;
}
