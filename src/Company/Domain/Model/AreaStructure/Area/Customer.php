<?php

namespace Company\Domain\Model\AreaStructure\Area;

use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineCustomerRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObjectList;
use Sales\Domain\Model\AreaStructure\Area;
use Sales\Domain\Model\AreaStructure\Area\Customer\VerificationReport;

#[Entity(repositoryClass: DoctrineCustomerRepository::class)]
class Customer
{
    #[FetchableObject(targetEntity: Area::class, joinColumnName: "Area_id")]
    #[ManyToOne(targetEntity: Area::class)]
    #[JoinColumn(name: "Area_id", referencedColumnName: "id")]
    protected Area $area;

    #[Id, Column(type: "guid")]
    protected string $id;

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

    //QUERY ONLY
    #[FetchableObjectList(targetEntity: VerificationReport::class, joinColumnName: "Customer_id", paginationRequired: false)]
    protected $verificationReports;

    protected function __construct()
    {
    }
}
