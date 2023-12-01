<?php

namespace Manager\Domain\Model\AreaStructure\Area;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Manager\Domain\Model\AreaStructure\Area;
use Manager\Infrastructure\Persistence\Doctrine\Repository\DoctrineCustomerRepository;

#[Entity(repositoryClass: DoctrineCustomerRepository::class)]
class Customer
{

    #[ManyToOne(targetEntity: Area::class, fetch: "LAZY")]
    #[JoinColumn(name: "Area_id", referencedColumnName: "id")]
    protected Area $area;
    
    #[Id, Column(type: "guid")]
    protected string $id;

    public function getId(): string
    {
        return $this->id;
    }

    protected function __construct()
    {
        
    }
    
    public function areaEquals(Area $area): bool
    {
        return $this->area === $area;
    }
}
