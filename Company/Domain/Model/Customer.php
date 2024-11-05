<?php

namespace Company\Domain\Model;

use Company\Domain\Model\Customer\VerificationReport;
use Company\Domain\Model\Manager\Sales\CustomerAssignment;
use Company\Domain\Model\Province\City;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineCustomerRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Resources\Exception\RegularException;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObjectList;
use Resources\ValidationRule;
use Resources\ValidationService;
use Shared\Domain\Enum\CustomerAssignmentStatus;
use Shared\Domain\Enum\CustomerStatus;

#[Entity(repositoryClass: DoctrineCustomerRepository::class)]
class Customer
{

    #[FetchableObject(targetEntity: City::class, joinColumnName: "City_id")]
    #[ManyToOne(targetEntity: City::class)]
    #[JoinColumn(name: "City_id", referencedColumnName: "id")]
    protected ?City $city;

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $disabled;

    #[Column(type: "datetimetz_immutable", nullable: true, options: ["default" => "CURRENT_TIMESTAMP"])]
    protected DateTimeImmutable $createdTime;
    
    #[Column(type: "string", enumType: CustomerStatus::class, options: ["default" => CustomerStatus::NEW->value])]
    protected CustomerStatus $status;

    #[Column(type: "string", length: 255, nullable: false)]
    protected string $name;

    #[Column(type: "string", length: 255, nullable: true)]
    protected ?string $email;

    #[Column(type: "string", length: 255, nullable: false, unique:true)]
    protected string $phone;

    #[Column(type: "string", length: 255, nullable: true)]
    protected ?string $source;

    #[FetchableObjectList(targetEntity: CustomerAssignment::class, joinColumnName: "Customer_id",
                paginationRequired: true)]
    #[OneToMany(targetEntity: CustomerAssignment::class, mappedBy: "customer", fetch: "EXTRA_LAZY")]
    protected Collection $customerAssignments;

    //QUERY ONLY
    #[FetchableObjectList(targetEntity: VerificationReport::class, joinColumnName: "Customer_id",
                paginationRequired: false)]
    protected $verificationReports;

    //
    protected function setName(string $name)
    {
        ValidationService::build()
                ->addRule(ValidationRule::notEmpty())
                ->execute($name, 'customer name is mandatory');
        $this->name = $name;
    }

    protected function setPhone(string $phone)
    {
        ValidationService::build()
                ->addRule(ValidationRule::phone())
                ->execute($phone, 'valid customer phone is mandatory');
        $this->phone = $phone;
    }

    public function setEmail(?string $email)
    {
        ValidationService::build()
                ->addRule(ValidationRule::optional(ValidationRule::email()))
                ->execute($email, 'invalid customer mail address format');
        $this->email = $email;
    }

    //
    public function getId(): string
    {
        return $this->id;
    }

    //
    public function __construct(?City $city, string $id, CustomerData $data)
    {
        $this->city = $city;
        $this->id = $id;
        $this->disabled = false;
        $this->createdTime = new DateTimeImmutable();
        $this->status = CustomerStatus::NEW;
        $this->setName($data->name);
        $this->setPhone($data->phone);
        $this->setEmail($data->email);
        $this->source = $data->source ?? null;
        //
        $this->city?->assertActive();
    }

    //
    public function assertHasNoActiveAssignment(): void
    {
        if ($this->hasActiveAssignment()) {
            throw RegularException::forbidden('customer already being maintained');
        }
    }

    public function hasActiveAssignment(): bool
    {
        $criteria = Criteria::create()
                ->andWhere(Criteria::expr()->eq('status', CustomerAssignmentStatus::ACTIVE));
        return !$this->customerAssignments->matching($criteria)->isEmpty();
    }
}
