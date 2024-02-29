<?php

namespace Company\Domain\Model\AreaStructure\Area;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;

readonly class CustomerData extends AbstractEntityMutationPayload
{

    public ?string $areaId;
    public ?string $name;
    public ?string $phone;
    public ?string $email;
    public ?string $source;

    public function setAreaId(?string $areaId)
    {
        $this->areaId = $areaId;
        return $this;
    }

    public function setName(?string $name)
    {
        $this->name = $name;
        return $this;
    }

    public function setPhone(?string $phone)
    {
        $this->phone = $phone;
        return $this;
    }

    public function setEmail(?string $email)
    {
        $this->email = $email;
        return $this;
    }

    public function setSource(?string $source)
    {
        $this->source = $source;
        return $this;
    }
}
