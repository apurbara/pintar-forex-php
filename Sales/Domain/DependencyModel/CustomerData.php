<?php

namespace Sales\Domain\DependencyModel;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;

readonly class CustomerData extends AbstractEntityMutationPayload
{

    public ?string $cityId;
    public ?string $name;
    public ?string $email;
    public ?string $phone;
    public ?string $source;

    public function setCityId(?string $cityId)
    {
        $this->cityId = $cityId;
        return $this;
    }

    public function setName(?string $name)
    {
        $this->name = $name;
        return $this;
    }

    public function setEmail(?string $email)
    {
        $this->email = $email;
        return $this;
    }

    public function setPhone(?string $phone)
    {
        $this->phone = $phone;
        return $this;
    }

    public function setSource(?string $source)
    {
        $this->source = $source;
        return $this;
    }
}
