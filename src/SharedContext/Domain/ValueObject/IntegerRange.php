<?php

namespace SharedContext\Domain\ValueObject;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;
use Resources\Exception\RegularException;

#[Embeddable]
class IntegerRange
{

    #[Column(type: "integer", nullable: true)]
    protected ?int $minimumValue = null;

    #[Column(type: "integer", nullable: true)]
    protected ?int $maximumValue = null;

    protected function setMinimumValue(?int $minimumValue): void
    {
        $this->minimumValue = $minimumValue;
    }

    protected function setMaximumValue(?int $maximumValue): void
    {
        $this->maximumValue = $maximumValue;
    }

    public function __construct(IntegerRangeData $integerRangeData)
    {
        $this->minimumValue = $integerRangeData->minimumValue;
        $this->maximumValue = $integerRangeData->maximumValue;

        if (isset($this->maximumValue) && $this->maximumValue < $this->minimumValue) {
            throw RegularException::forbidden('forbidden: max value must be bigger than min value');
        }
    }

    public function sameValueAs(IntegerRange $other): bool
    {
        return $this->minimumValue == $other->minimumValue && $this->maximumValue == $other->maximumValue;
    }

    public function contain(float $value): bool
    {
        return $this->actualMinValue() <= $value && $value <= $this->actualMaxValue();
    }

    protected function actualMinValue()
    {
        return empty($this->minimumValue) ? -INF : $this->minimumValue;
    }

    protected function actualMaxValue()
    {
        return empty($this->maximumValue) ? INF : $this->maximumValue;
    }
}
