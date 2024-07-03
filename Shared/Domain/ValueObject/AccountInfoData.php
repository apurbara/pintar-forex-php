<?php

namespace Shared\Domain\ValueObject;

readonly class AccountInfoData
{

    public function __construct(
            public ?string $name, public ?string $email, public ?string $password
    )
    {
        
    }
}
