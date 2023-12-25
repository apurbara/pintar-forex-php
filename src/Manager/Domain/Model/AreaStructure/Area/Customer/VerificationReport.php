<?php

namespace Manager\Domain\Model\AreaStructure\Area\Customer;

use Company\Domain\Model\CustomerVerification as CustomerVerificationInCompanyBC;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Manager\Domain\Model\AreaStructure\Area\Customer;
use Manager\Infrastructure\Persistence\Doctrine\Repository\DoctrineVerificationReportRepository;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;

#[Entity(repositoryClass: DoctrineVerificationReportRepository::class)]
class VerificationReport
{

    //query purpose
    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected DateTimeImmutable $createdTime;

    #[Column(type: "text", nullable: true)]
    protected ?string $note;

    #[FetchableObject(targetEntity: Customer::class, joinColumnName: "Customer_id")]
    #[JoinColumn(name: "Customer_id", referencedColumnName: "id")]
    protected $customer;

    #[FetchableObject(targetEntity: CustomerVerificationInCompanyBC::class, joinColumnName: "CustomerVerification_id")]
    #[JoinColumn(name: "CustomerVerification_id", referencedColumnName: "id")]
    protected $customerVerification;

    protected function __construct()
    {
        
    }
}
