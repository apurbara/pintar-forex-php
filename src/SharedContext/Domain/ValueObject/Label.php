<?php

namespace SharedContext\Domain\ValueObject;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;
use Resources\ValidationRule;
use Resources\ValidationService;

#[Embeddable]
class Label
{

    #[Column(type: "string", length: 255, nullable: false)]
    protected string $name;

    #[Column(type: "string", length: 1024, nullable: true)]
    protected ?string $description = null;

    protected function setName(string $title): void
    {
        ValidationService::build()
                ->addRule(ValidationRule::notEmpty())
                ->execute($title, 'bad request: title is mandatory');
        $this->name = $title;
    }

    public function __construct(LabelData $labelData)
    {
        $this->setName($labelData->name);
        $this->description = $labelData->description;
    }

    public function update(LabelData $labelData): self
    {
        return new static($labelData);
    }

    public function sameValueAs(self $other): bool
    {
        return $this == $other;
    }
}
