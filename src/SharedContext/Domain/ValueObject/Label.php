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
    protected string $title;

    #[Column(type: "string", length: 1024, nullable: true)]
    protected ?string $description = null;

    protected function setTitle(string $title): void
    {
        ValidationService::build()
                ->addRule(ValidationRule::notEmpty())
                ->execute($title, 'bad request: title is mandatory');
        $this->title = $title;
    }

    public function __construct(LabelData $labelData)
    {
        $this->setTitle($labelData->title);
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
