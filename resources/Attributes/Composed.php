<?php

declare(strict_types=1);

namespace Resources\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class Composed
{

    /**
     * @var string|null
     * @readonly
     */
    public $class;

    /**
     * @var string|bool|null
     * @readonly
     */
    public $columnPrefix;

    public function __construct(?string $class = null, $columnPrefix = null)
    {
        $this->class = $class;
        $this->columnPrefix = $columnPrefix;
    }
}
