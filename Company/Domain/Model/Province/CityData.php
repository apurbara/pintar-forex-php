<?php

namespace Company\Domain\Model\Province;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;

readonly class CityData extends AbstractEntityMutationPayload
{

    public string $provinceId;
    public string $name;

    public function setProvinceId(string $provinceId)
    {
        $this->provinceId = $provinceId;
        return $this;
    }

    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }
}
