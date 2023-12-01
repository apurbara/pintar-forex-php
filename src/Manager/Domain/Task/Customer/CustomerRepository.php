<?php

namespace Manager\Domain\Task\Customer;

use Manager\Domain\Model\AreaStructure\Area\Customer;

interface CustomerRepository
{

    public function ofId(string $id): Customer;
}
