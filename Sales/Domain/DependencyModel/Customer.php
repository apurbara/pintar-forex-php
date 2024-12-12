<?php

namespace Sales\Domain\DependencyModel;

use Company\Domain\Model\Province\City as City2;
use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Resources\Exception\RegularException;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObjectList;
use Resources\Uuid;
use Resources\ValidationRule;
use Resources\ValidationService;
use Sales\Domain\DependencyModel\Customer\VerificationReport;
use Sales\Domain\DependencyModel\Customer\VerificationReportData;
use Sales\Domain\DependencyModel\Province\City;
use Sales\Domain\Model\Sales\FactFindingAssignment;
use Sales\Domain\Model\Sales\GreetingAssignment;
use Sales\Domain\Model\Sales\StrikingAssignment;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineCustomerRepository;
use Shared\Domain\Enum\CustomerStatus;

#[Entity(repositoryClass: DoctrineCustomerRepository::class)]
class Customer
{

    #[FetchableObject(targetEntity: City2::class, joinColumnName: "City_id")]
    #[ManyToOne(targetEntity: City::class)]
    #[JoinColumn(name: "City_id", referencedColumnName: "id")]
    protected ?City $city;

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "datetimetz_immutable", nullable: false, options: ["default" => "CURRENT_TIMESTAMP"])]
    protected DateTimeImmutable $createdTime;

    #[Column(type: "string", enumType: CustomerStatus::class, options: ["default" => CustomerStatus::NEW->value])]
    protected CustomerStatus $status;

    #[Column(type: "string", length: 255, nullable: false)]
    protected string $name;
    
    #[Column(type: "text", nullable: true)]
    protected ?string $bio;

    #[Column(type: "string", length: 255, nullable: true)]
    protected ?string $email;

    #[Column(type: "string", length: 255, nullable: false)]
    protected string $phone;

    #[Column(type: "string", length: 255, nullable: true)]
    protected ?string $source;

    #[Column(type: "smallint", nullable: true)]
    protected ?int $rating;

    #[FetchableObjectList(targetEntity: VerificationReport::class, joinColumnName: "Customer_id", paginationRequired: false)]
    #[OneToMany(targetEntity: VerificationReport::class, mappedBy: "customer", cascade: ["persist"])]
    protected Collection $verificationReports;
    
    //QUERY
    #[FetchableObjectList(targetEntity: GreetingAssignment::class, joinColumnName: "Customer_id", paginationRequired: false)]
    protected $greetingAssignments;
    #[FetchableObjectList(targetEntity: FactFindingAssignment::class, joinColumnName: "Customer_id", paginationRequired: false)]
    protected $factFindingAssignments;
    #[FetchableObjectList(targetEntity: StrikingAssignment::class, joinColumnName: "Customer_id", paginationRequired: false)]
    protected $strikingAssignments;

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

    protected function __construct()
    {
        
    }

    public function update(?City $city, CustomerData $data): void
    {
        $city?->assertActive();
        //
        $this->city = $city;
        $this->setEmail($data->email);
        $this->setName($data->name);
        $this->bio = $data->bio;
    }

    public function updateRating(int $rating): void
    {
        if ($rating > 5 || $rating < 0) {
            throw RegularException::badRequest('invalid rating value');
        }
        $this->rating = $rating;
    }

    public function recycle(): void
    {
        $this->status = match ($this->status) {
            CustomerStatus::NEW, CustomerStatus::RECYCLED => CustomerStatus::RECYCLED,
            default => throw RegularException::forbidden('unable to invalidate customer'),
        };
    }

    public function validate(): void
    {
        $this->status = match ($this->status) {
            CustomerStatus::NEW, CustomerStatus::RECYCLED => CustomerStatus::FACT_FINDING_REQUIRED,
            default => throw RegularException::forbidden('unable to validate customer'),
        };
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
            $verificationReport = new VerificationReport(
                    $this, $customerVerification, Uuid::generateUuid4(), $verificationReportData);
            $this->verificationReports->add($verificationReport);
        }
        $verificationReportData->setId($verificationReport->getId());
    }

    /**
     * 
     * @param CustomerVerification[] $allActiveCustomerVerification
     * @return void
     */
    public function markVerificationComplete(array $allActiveCustomerVerifications): void
    {
        foreach ($allActiveCustomerVerifications as $customerVerification) {
            $p = fn(VerificationReport $verificationReport) => $verificationReport->associateWithCustomerVerification($customerVerification);
            if ($this->verificationReports->filter($p)->isEmpty()) {
                throw RegularException::forbidden('incomplete verfication report');
            }
        }

        $this->status = match ($this->status) {
            CustomerStatus::FACT_FINDING_REQUIRED => CustomerStatus::STRIKING_REQUIRED,
            default => throw RegularException::forbidden('unable to set customer to striking phase'),
        };
    }
}
