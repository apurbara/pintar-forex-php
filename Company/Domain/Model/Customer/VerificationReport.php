<?php

namespace Company\Domain\Model\Customer;

use Company\Domain\Model\Customer;
use Company\Domain\Model\CustomerVerification;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineVerificationReportRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;

#[Entity(repositoryClass: DoctrineVerificationReportRepository::class)]
class VerificationReport
{

    #[FetchableObject(targetEntity: Customer::class, joinColumnName: "Customer_id")]
    #[ManyToOne(targetEntity: Customer::class, inversedBy: "verificationReports", fetch: "LAZY")]
    #[JoinColumn(name: "Customer_id", referencedColumnName: "id")]
    protected Customer $customer;

    #[FetchableObject(targetEntity: CustomerVerification::class, joinColumnName: "CustomerVerification_id")]
    #[ManyToOne(targetEntity: CustomerVerification::class)]
    #[JoinColumn(name: "CustomerVerification_id", referencedColumnName: "id")]
    protected CustomerVerification $customerVerification;

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected DateTimeImmutable $createdTime;

    #[Column(type: "text", nullable: true)]
    protected ?string $note;
    
    protected function __construct()
    {
    }

}
