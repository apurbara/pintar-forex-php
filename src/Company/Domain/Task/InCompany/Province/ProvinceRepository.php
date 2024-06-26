<?php

namespace Company\Domain\Task\InCompany\Province;

use Company\Domain\Model\Province;

interface ProvinceRepository
{

    public function nextIdentity(): string;

    public function add(Province $province): void;

    public function ofId(string $id): Province;

    //
    public function provinceList(array $paginationSchema);

    public function allProvince(array $searchSchema);
}
