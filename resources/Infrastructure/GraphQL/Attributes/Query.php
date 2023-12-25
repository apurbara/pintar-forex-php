<?php

declare(strict_types=1);

namespace Resources\Infrastructure\GraphQL\Attributes;

use Attribute;

#[Attribute]
final class Query
{
    public const PAGINATION_RESPONSE_WRAPPER = "PAGINATION";
    public const LIST_RESPONSE_WRAPPER = "LIST";

//    public readonly ?string $inputType;
    
    /**
     * 
     * @var string|null
     * possible value:
     * NULL => single response
     * ALL => list response without pagination
     * PAGINATION => list response with pagination wrapper 
     */
    public readonly ?string $responseWrapper;

//    public readonly ?string $inputName;
    public readonly ?string $responseType;
//
//    public function __construct(?string $inputType = null, ?string $inputName = null, ?string $responseType = null)
//    {
//        $this->inputType = $inputType;
//        $this->inputName = $inputName;
//        $this->responseType = $responseType;
//    }

    public function __construct(?string $responseWrapper = null, ?string $responseType)
    {
//        $this->inputType = $inputType;
        $this->responseWrapper = $responseWrapper;
        $this->responseType = $responseType;
    }

}
