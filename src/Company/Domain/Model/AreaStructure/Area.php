<?php

namespace Company\Domain\Model\AreaStructure;

use Company\Domain\Model\AreaStructure;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineAreaRepository;
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

#[Entity(repositoryClass: DoctrineAreaRepository::class)]
class Area
{

    #[FetchableEntity(targetEntity: AreaStructure::class, joinColumnName: 'AreaStructure_id')]
    #[ManyToOne(targetEntity: AreaStructure::class)]
    #[JoinColumn(name: "AreaStructure_id", referencedColumnName: "id")]
    protected AreaStructure $areaStructure;
    
    #[Id, Column(type: "guid")]
    protected string $id;
    
    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $disabled;
    
    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected DateTimeImmutable $createdTime;
    
    #[Embedded(class: Label::class, columnPrefix: false)]
    protected Label $label;
    
    #[FetchableEntity(targetEntity: Area::class, joinColumnName: 'Area_idOfParent')]
    #[ManyToOne(targetEntity: Area::class)]
    #[JoinColumn(name: "Area_idOfParent", referencedColumnName: "id")]
    protected ?Area $parent;

    public function __construct(AreaStructure $areaStructure, AreaData $data)
    {
        $this->areaStructure = $areaStructure;
        $this->id = $data->id;
        $this->disabled = false;
        $this->createdTime = new \DateTimeImmutable();
        $this->label = new Label($data->labelData);
        $this->parent = null;
    }

    //
    public function createChild(AreaStructure $childAreaStructure, AreaData $childData): static
    {
        if ($this->disabled) {
            throw RegularException::forbidden('can only create child of active area');
        }
        if (!$childAreaStructure->isActiveChildOfParent($this->areaStructure)) {
            throw RegularException::forbidden('child area must associate with active structure descendant');
        }
        $area = new static($childAreaStructure, $childData);
        $area->parent = $this;
        return $area;
    }
}
