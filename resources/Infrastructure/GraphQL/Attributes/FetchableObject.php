<?php

declare(strict_types=1);

namespace Resources\Infrastructure\GraphQL\Attributes;

use Attribute;

#[Attribute]
final class FetchableObject
{

    public string $targetEntity;

    /**
     * 
     * join column; usullay 'Target_id'
     * assuming target column reference is always 'id'
     * @var string
     */
    public string $joinColumnName;
    public ?string $referenceColumnName;

    public function __construct(string $targetEntity, string $joinColumnName, ?string $referenceColumnName = null)
    {
        $this->targetEntity = $targetEntity;
        $this->joinColumnName = $joinColumnName;
        $this->referenceColumnName = $referenceColumnName;
    }
}
