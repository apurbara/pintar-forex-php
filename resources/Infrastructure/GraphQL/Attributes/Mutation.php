<?php

declare(strict_types=1);

namespace Resources\Infrastructure\GraphQL\Attributes;

use Attribute;

#[Attribute]
final class Mutation
{

    public readonly ?string $responseType;

    public function __construct(?string $responseType = null)
    {
        $this->responseType = $responseType;
    }

//    public readonly ?string $inputType;
//    public readonly ?string $inputName;
//
//    public function __construct(?string $responseType = null, ?string $inputType = null, ?string $inputName = null)
//    {
//        $this->responseType = $responseType;
//        $this->inputType = $inputType;
//        $this->inputName = $inputName;
//    }
}
