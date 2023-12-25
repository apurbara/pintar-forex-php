<?php

namespace User\Domain\Model\Personnel;

use Company\Domain\Model\AreaStructure\Area as AreaInCompanyBC;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;
use SharedContext\Domain\Enum\SalesType;
use User\Domain\Model\Personnel;
use User\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesRepository;

#[Entity(repositoryClass: DoctrineSalesRepository::class)]
class Sales
{
    #[FetchableObject(targetEntity: Manager::class, joinColumnName: "Manager_id")]
    #[ManyToOne(targetEntity: Manager::class)]
    #[JoinColumn(name: "Manager_id", referencedColumnName: "id")]
    protected Manager $manager;
    
    #[FetchableObject(targetEntity: Personnel::class, joinColumnName: "Personnel_id")]
    #[ManyToOne(targetEntity: Personnel::class)]
    #[JoinColumn(name: "Personnel_id", referencedColumnName: "id")]
    protected Personnel $personnel;
    
    #[FetchableObject(targetEntity: AreaInCompanyBC::class, joinColumnName: "Area_id")]
    #[JoinColumn(name: "Area_id", referencedColumnName: "id")]
    protected $area;
    
    #[Id, Column(type: "guid")]
    protected string $id;
    
    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected DateTimeImmutable $createdTime;
    
    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $disabled;
    
    #[Column(type: "string", enumType: SalesType::class)]
    protected SalesType $type;
    
    protected function __construct()
    {
    }
}
