<?php

namespace Sales\Domain\Task\Area;

use Sales\Domain\Model\AreaStructure\Area;

interface AreaRepository
{

    public function ofId(string $id): Area;
}
