<?php

namespace Company\Domain\Model;

use Company\Domain\Model\AreaStructure\Area;
use Company\Domain\Model\AreaStructure\AreaData;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineAreaStructureRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Resources\Attributes\FetchableEntity;
use Resources\Exception\RegularException;
use SharedContext\Domain\ValueObject\Label;

#[Entity(repositoryClass: DoctrineAreaStructureRepository::class)]
class AreaStructure
{

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $disabled;

    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected DateTimeImmutable $createdTime;

    #[Embedded(class: Label::class, columnPrefix: false)]
    protected Label $label;

    #[FetchableEntity(targetEntity: AreaStructure::class, joinColumnName: 'AreaStructure_idOfParent')]
    #[ManyToOne(targetEntity: AreaStructure::class)]
    #[JoinColumn(name: "AreaStructure_idOfParent", referencedColumnName: "id")]
    protected ?AreaStructure $parent;

    public function __construct(AreaStructureData $data)
    {
        $this->id = $data->id;
        $this->disabled = false;
        $this->createdTime = new \DateTimeImmutable();
        $this->label = new Label($data->labelData);
        $this->parent = null;
    }

    //
    public function createChild(AreaStructureData $data): static
    {
        if ($this->disabled) {
            throw RegularException::forbidden('can only create child of active area structure');
        }
        $child = new static($data);
        $child->parent = $this;
        return $child;
    }

    public function createRootArea(AreaData $areaData): Area
    {
        if ($this->disabled || $this->parent) {
            throw RegularException::forbidden('can only create root area in active root structure');
        }
        return new Area($this, $areaData);
    }

    public function isActiveChildOfParent(AreaStructure $parent): bool
    {
        return !$this->disabled && $this->parent === $parent;
    }
}
