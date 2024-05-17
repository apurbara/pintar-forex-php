<?php

namespace Company\Domain\Model\AreaStructure;

use Company\Domain\Model\AreaStructure;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineAreaRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Resources\Exception\RegularException;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObjectList;
use SharedContext\Domain\ValueObject\Label;

#[Entity(repositoryClass: DoctrineAreaRepository::class)]
class Area
{

    #[FetchableObject(targetEntity: AreaStructure::class, joinColumnName: 'AreaStructure_id')]
    #[ManyToOne(targetEntity: AreaStructure::class, inversedBy: "areas", fetch: "EXTRA_LAZY")]
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

    #[FetchableObject(targetEntity: Area::class, joinColumnName: 'Area_idOfParent')]
    #[ManyToOne(targetEntity: Area::class, inversedBy: "children", fetch: "EXTRA_LAZY")]
    #[JoinColumn(name: "Area_idOfParent", referencedColumnName: "id")]
    protected ?Area $parent;

    #[FetchableObjectList(targetEntity: Area::class, joinColumnName: "Area_idOfParent", paginationRequired: false)]
    #[OneToMany(targetEntity: Area::class, mappedBy: "parent", fetch: "EXTRA_LAZY")]
    protected Collection $children;

    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    public function __construct(AreaStructure $areaStructure, AreaData $data)
    {
        $this->areaStructure = $areaStructure;
        $this->id = $data->id;
        $this->disabled = false;
        $this->createdTime = new \DateTimeImmutable();
        $this->label = new Label($data->labelData);
        $this->parent = null;
        //
        $this->areaStructure->assertActive();
    }

    public function update(AreaData $data): void
    {
        $this->label = new Label($data->labelData);
    }

    public function disable(): void
    {
        $criteria = Criteria::create()
                ->andWhere(Criteria::expr()->eq('disabled', false));
        if (!$this->children->matching($criteria)->isEmpty()) {
            throw RegularException::forbidden('area has active child');
        }
        $this->disabled = true;
    }

    //
    public function createChild(AreaStructure $childAreaStructure, AreaData $childData): static
    {
        $this->assertActive();
        if (!$childAreaStructure->isChildOf($this->areaStructure)) {
            throw RegularException::forbidden('child area must associate with active structure descendant');
        }
        $area = new static($childAreaStructure, $childData);
        $area->parent = $this;
        return $area;
    }

    //
    public function assertActive(): void
    {
        if ($this->disabled) {
            throw RegularException::forbidden('inactive area');
        }
    }
}
