<?php

namespace Company\Domain\Model\Personnel\Manager;

use Company\Domain\Model\AreaStructure\Area;
use Company\Domain\Model\Personnel;
use Company\Domain\Model\Personnel\Manager;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Resources\Attributes\FetchableEntity;
use SharedContext\Domain\Enum\SalesType;

#[Entity(repositoryClass: DoctrineSalesRepository::class)]
class Sales
{
    #[FetchableEntity(targetEntity: Manager::class, joinColumnName: "Manager_id")]
    #[ManyToOne(targetEntity: Manager::class)]
    #[JoinColumn(name: "Manager_id", referencedColumnName: "id")]
    protected Manager $manager;
    
    #[FetchableEntity(targetEntity: Personnel::class, joinColumnName: "Personnel_id")]
    #[ManyToOne(targetEntity: Personnel::class)]
    #[JoinColumn(name: "Personnel_id", referencedColumnName: "id")]
    protected Personnel $personnel;
    
    #[FetchableEntity(targetEntity: Area::class, joinColumnName: "Area_id")]
    #[ManyToOne(targetEntity: Area::class)]
    #[JoinColumn(name: "Area_id", referencedColumnName: "id")]
    protected Area $area;
    
    #[Id, Column(type: "guid")]
    protected string $id;
    
    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected DateTimeImmutable $createdTime;
    
    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $disabled;
    
    #[Column(type: "string", enumType: SalesType::class)]
    protected SalesType $type;
    
    public function __construct(Manager $manager, Personnel $personnel, Area $area, SalesData $data)
    {
        $this->manager = $manager;
        $this->personnel = $personnel;
        $this->area = $area;
        $this->id = $data->id;
        $this->createdTime = new DateTimeImmutable();
        $this->disabled = false;
        $this->type = SalesType::from($data->type);
    }

}
