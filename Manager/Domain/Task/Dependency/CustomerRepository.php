<?php

namespace Manager\Domain\Task\Dependency;

use Manager\Domain\DependencyModel\Customer;

interface CustomerRepository
{

    public function ofId(string $id): Customer;
}
