<?php

namespace Resources\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class FetchableEntity
{

    /**
     * @var string
     * @readonly
     */
    public $targetEntity;
    
    /**
     * @var string
     * @readonly
     */
    public $joinColumnName;

    public function __construct(string $targetEntity, string $joinColumnName)
    {
        $this->targetEntity = $targetEntity;
        $this->joinColumnName = $joinColumnName;
    }
}
