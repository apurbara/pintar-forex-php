<?php

namespace Sales\Domain\Task\Dependency;

use Sales\Domain\DependencyModel\AreaStructure\Area;

interface AreaRepository
{

    public function ofId(string $id): Area;
}
