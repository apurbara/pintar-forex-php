<?php

namespace Shared\Domain\ValueObject;

readonly class ChangeUserPasswordData
{

    public function __construct(public string $previousPassword, public string $newPassword)
    {
        
    }
}
