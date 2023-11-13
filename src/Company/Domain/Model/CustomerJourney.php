<?php

namespace Company\Domain\Model;

use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineCustomerJourneyRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use SharedContext\Domain\ValueObject\Label;

#[Entity(repositoryClass: DoctrineCustomerJourneyRepository::class)]
class CustomerJourney
{

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $disabled;

    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected DateTimeImmutable $createdTime;

    #[Embedded(class: Label::class, columnPrefix: false)]
    protected Label $label;

    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $initial;

    public function __construct(string $id, CustomerJourneyData $data)
    {
        $this->id = $id;
        $this->disabled = false;
        $this->createdTime = new \DateTimeImmutable();
        $this->label = new Label($data->labelData);
        $this->initial = $data->initial ?? false;
    }

    public function update(CustomerJourneyData $data): void
    {
        $this->label = new Label($data->labelData);
    }
}
