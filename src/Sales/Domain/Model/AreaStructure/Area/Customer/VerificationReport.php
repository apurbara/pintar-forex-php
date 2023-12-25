<?php

namespace Sales\Domain\Model\AreaStructure\Area\Customer;

use Company\Domain\Model\CustomerVerification as CustomerVerificationInCompanyBC;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;
use Sales\Domain\Model\AreaStructure\Area\Customer;
use Sales\Domain\Model\CustomerVerification;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineVerificationReportRepository;

#[Entity(repositoryClass: DoctrineVerificationReportRepository::class)]
class VerificationReport
{

    #[FetchableObject(targetEntity: Customer::class, joinColumnName: "Customer_id")]
    #[ManyToOne(targetEntity: Customer::class, inversedBy: "verificationReports", fetch: "LAZY")]
    #[JoinColumn(name: "Customer_id", referencedColumnName: "id")]
    protected Customer $customer;

    #[FetchableObject(targetEntity: CustomerVerificationInCompanyBC::class, joinColumnName: "CustomerVerification_id")]
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
            Customer $customer, CustomerVerification $customerVerification, VerificationReportData $data)
    {
        $customerVerification->assertActive();
        //
        $this->customer = $customer;
        $this->customerVerification = $customerVerification;
        $this->id = $data->id;
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
