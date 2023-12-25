<?php

namespace Manager\Domain\Model\AreaStructure\Area;

use Company\Domain\Model\AreaStructure\Area as AreaInCompanyBC;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Manager\Domain\Model\AreaStructure\Area;
use Manager\Domain\Model\AreaStructure\Area\Customer\VerificationReport;
use Manager\Infrastructure\Persistence\Doctrine\Repository\DoctrineCustomerRepository;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObjectList;

#[Entity(repositoryClass: DoctrineCustomerRepository::class)]
class Customer
{
    #[FetchableObject(targetEntity: AreaInCompanyBC::class, joinColumnName: "Area_id")]
    #[ManyToOne(targetEntity: Area::class)]
    #[JoinColumn(name: "Area_id", referencedColumnName: "id")]
    protected Area $area;

    #[Id, Column(type: "guid")]
    protected string $id;

    //query purpose
    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $disabled;

    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected DateTimeImmutable $createdTime;

    #[Column(type: "string", length: 255, nullable: false)]
    protected string $name;

    #[Column(type: "string", length: 255, nullable: true)]
    protected string $email;
    
    #[Column(type: "string", length: 255, nullable: true)]
    protected string $phone;

    #[FetchableObjectList(targetEntity: VerificationReport::class, joinColumnName: "Customer_id", paginationRequired: false)]
    protected $verificationReports;

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
