<?php

declare(strict_types=1);

namespace Resources\Infrastructure\GraphQL\Attributes;

use Attribute;

#[Attribute]
final class FetchableObjectList
{

    public string $targetEntity;

    /**
     * children join column; usually 'Parent_id'
     * assuming parent column for reference is always 'id'
     * @var string
     */
    public string $joinColumnName;
    public bool $paginationRequired = false;

    public function __construct(string $targetEntity, string $joinColumnName,
            bool $paginationRequired)
    {
        $this->targetEntity = $targetEntity;
        $this->joinColumnName = $joinColumnName;
        $this->paginationRequired = $paginationRequired;
    }
}
