<?php

namespace Sales\Domain\DependencyModel;

use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Resources\Uuid;
use Resources\ValidationRule;
use Resources\ValidationService;
use Sales\Domain\DependencyModel\Customer\VerificationReport;
use Sales\Domain\DependencyModel\Customer\VerificationReportData;
use Sales\Domain\DependencyModel\Province\City;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineCustomerRepository;

#[Entity(repositoryClass: DoctrineCustomerRepository::class)]
class Customer
{
    #[ManyToOne(targetEntity: City::class)]
    #[JoinColumn(name: "City_id", referencedColumnName: "id")]
    protected City $city;

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $disabled;

    #[Column(type: "datetimetz_immutable", nullable: false, options: ["default" => "CURRENT_TIMESTAMP"])]
    protected DateTimeImmutable $createdTime;

    #[Column(type: "string", length: 255, nullable: false)]
    protected string $name;

    #[Column(type: "string", length: 255, nullable: true)]
    protected ?string $email;
    
    #[Column(type: "string", length: 255, nullable: false)]
    protected string $phone;
    
    #[Column(type: "string", length: 255, nullable: true)]
    protected ?string $source;

    #[OneToMany(targetEntity: VerificationReport::class, mappedBy: "customer", cascade: ["persist"])]
    protected Collection $verificationReports;

    protected function setName(string $name)
    {
        ValidationService::build()
                ->addRule(ValidationRule::notEmpty())
                ->execute($name, 'customer name is mandatory');
        $this->name = $name;
    }

    protected function setEmail(?string $email)
    {
        ValidationService::build()
                ->addRule(ValidationRule::optional(ValidationRule::email()))
                ->execute($email, 'customer email must be in valid email address format');
        $this->email = $email;
    }

    protected function setPhone(string $phone)
    {
        ValidationService::build()
                ->addRule(ValidationRule::phone())
                ->execute($phone, 'customer phone is mandatory and must be in valid phone format');
        $this->phone = $phone;
    }

    public function __construct(City $city, string $id, CustomerData $data)
    {
        $this->city = $city;
        $this->id = $id;
        $this->disabled = false;
        $this->createdTime = new DateTimeImmutable();
        $this->setName($data->name);
        $this->setEmail($data->email);
        $this->setPhone($data->phone);
        $this->source = $data->source ?? null;
        //
        $this->city->assertActive();
    }
    
    public function update(City $city, CustomerData $data): void
    {
        $this->city = $city;
        $this->setEmail($data->email);
        $this->setName($data->name);
    }
    
    //
    public function submitVerificationReport(
            CustomerVerification $customerVerification, VerificationReportData $verificationReportData): void
    {
        $p = fn(VerificationReport $verificationReport) => $verificationReport->associateWithCustomerVerification($customerVerification);
        $verificationReport = $this->verificationReports->filter($p)->first();
        if ($verificationReport) {
            $verificationReport->update($verificationReportData);
        } else {
            $verificationReportData->setId(Uuid::generateUuid4());
            $verificationReport = new VerificationReport($this, $customerVerification, $verificationReportData->id, $verificationReportData);
            $this->verificationReports->add($verificationReport);
        }
        
    }
}
