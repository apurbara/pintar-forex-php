<?php

namespace Sales\Domain\Task\Dependency;

use Sales\Domain\DependencyModel\Province\City;


interface CityRepository
{

    public function ofId(string $id): City;
}
