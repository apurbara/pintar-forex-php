<?php

namespace Company\Domain\Task\City;

use Company\Domain\Model\Province\City;

interface CityRepository
{

    public function nextIdentity(): string;

    public function add(City $city): void;

    public function ofId(string $id): City;

    //
    public function cityList(array $paginationSchema);

    public function allCity(array $searchSchema);
}
