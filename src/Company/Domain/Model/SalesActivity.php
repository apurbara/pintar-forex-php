<?php

namespace Company\Domain\Model;

use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesActivityRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Resources\Exception\RegularException;
use Resources\Infrastructure\GraphQL\Attributes\ExcludeFromInput;
use SharedContext\Domain\ValueObject\Label;

#[Entity(repositoryClass: DoctrineSalesActivityRepository::class)]
class SalesActivity
{

    #[Id, Column(type: "guid")]
    protected string $id;
    
    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $disabled;
    
    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected DateTimeImmutable $createdTime;
    
    #[Embedded(class: Label::class, columnPrefix: false)]
    protected Label $label;
    
    #[Column(type: "smallint", nullable: false, options: ["default" => 0, "unsigned" => false])]
    protected int $duration;
    
    #[ExcludeFromInput]
    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $initial;

    protected function setDuration(int $duration)
    {
        if ($duration > 60 || $duration < 1) {
            throw RegularException::badRequest('duration is mandatory and must not exceed 60');
        }
        $this->duration = $duration;
    }

    public function __construct(SalesActivityData $data, bool $initial = false)
    {
        $this->id = $data->id;
        $this->disabled = false;
        $this->createdTime = new DateTimeImmutable();
        $this->label = new Label($data->labelData);
        $this->setDuration($data->duration);
        $this->initial = $initial;
    }

    public function update(SalesActivityData $data): void
    {
        $this->label = new Label($data->labelData);
        $this->setDuration($data->duration);
    }
}
