<?php

namespace Company\Domain\Model;

use Company\Domain\Model\AreaStructure\Area;
use Company\Domain\Model\AreaStructure\AreaData;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineAreaStructureRepository;
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

    #[FetchableObject(targetEntity: AreaStructure::class, joinColumnName: "AreaStructure_idOfParent")]
    #[ManyToOne(targetEntity: AreaStructure::class)]
    #[JoinColumn(name: "AreaStructure_idOfParent", referencedColumnName: "id")]
    protected ?AreaStructure $parent;

    //
    #[FetchableObjectList(targetEntity: AreaStructure::class, joinColumnName: "AreaStructure_idOfParent",
                paginationRequired: false)]
    #[OneToMany(targetEntity: AreaStructure::class, mappedBy: "parent", fetch: "EXTRA_LAZY")]
    protected Collection $children;

    #[OneToMany(targetEntity: Area::class, mappedBy: "areaStructure", fetch: "EXTRA_LAZY")]
    protected Collection $areas;

    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    public function __construct(AreaStructureData $data)
    {
        $this->id = $data->id;
        $this->disabled = false;
        $this->createdTime = new \DateTimeImmutable();
        $this->label = new Label($data->labelData);
        $this->parent = null;
    }

    public function update(AreaStructureData $data): void
    {
        $this->label = new Label($data->labelData);
    }

    public function disable(): void
    {
        $criteria = Criteria::create()
                ->andWhere(Criteria::expr()->eq('disabled', false));
        
        if (!$this->children->matching($criteria)->isEmpty()) {
            throw RegularException::forbidden('area structure has active children');
        }
        if (!$this->areas->matching($criteria)->isEmpty()) {
            throw RegularException::forbidden('area structure has active area');
        }
        
        $this->disabled = true;
    }

    //
    public function assertActive(): void
    {
        if ($this->disabled) {
            throw RegularException::forbidden('inactive area structure');
        }
    }

    //
    public function createChild(AreaStructureData $data): static
    {
        $this->assertActive();
        $child = new static($data);
        $child->parent = $this;
        return $child;
    }

    public function createRootArea(AreaData $areaData): Area
    {
        if ($this->parent) {
            throw RegularException::forbidden('can only create root area in active root structure');
        }
        return new Area($this, $areaData);
    }

    public function isChildOf(AreaStructure $parent): bool
    {
        return $this->parent === $parent;
    }
}
