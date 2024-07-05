<?php

namespace Sales\Domain\DependencyModel\Customer;

use Company\Domain\Model\CustomerVerification as FetchableCustomerVerificationFromCompanyBC;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;
use Sales\Domain\DependencyModel\Customer;
use Sales\Domain\DependencyModel\CustomerVerification;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineVerificationReportRepository;

#[Entity(repositoryClass: DoctrineVerificationReportRepository::class)]
class VerificationReport
{

    #[ManyToOne(targetEntity: Customer::class, inversedBy: "verificationReports", fetch: "LAZY")]
    #[JoinColumn(name: "Customer_id", referencedColumnName: "id")]
    protected Customer $customer;

    #[FetchableObject(targetEntity: FetchableCustomerVerificationFromCompanyBC::class, joinColumnName: "CustomerVerification_id")]
    #[ManyToOne(targetEntity: CustomerVerification::class)]
    #[JoinColumn(name: "CustomerVerification_id", referencedColumnName: "id")]
    protected CustomerVerification $customerVerification;

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected DateTimeImmutable $createdTime;

    #[Column(type: "text", nullable: true)]
    protected ?string $note;

    public function __construct(
            Customer $customer, CustomerVerification $customerVerification, string $id, VerificationReportData $data)
    {
        $customerVerification->assertActive();
        //
        $this->customer = $customer;
        $this->customerVerification = $customerVerification;
        $this->id = $id;
        $this->createdTime = new DateTimeImmutable();
        $this->note = $data->note;
    }

    public function update(VerificationReportData $data): void
    {
        $this->note = $data->note;
    }

    //
    public function associateWithCustomerVerification(CustomerVerification $customerVerification): bool
    {
        return $this->customerVerification === $customerVerification;
    }
}
