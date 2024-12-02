<?php

namespace Manager\Domain\DependencyModel;

use Company\Domain\Model\Customer\VerificationReport;
use Company\Domain\Model\Province\City;
use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Manager\Domain\Model\Manager\Sales\FactFindingAssignment;
use Manager\Domain\Model\Manager\Sales\GreetingAssignment;
use Manager\Domain\Model\Manager\Sales\StrikingAssignment;
use Manager\Infrastructure\Persistence\Doctrine\Repository\DoctrineCustomerRepository;
use Resources\Exception\RegularException;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObjectList;
use Shared\Domain\Enum\CustomerAssignmentStatus;
use Shared\Domain\Enum\CustomerStatus;

#[Entity(repositoryClass: DoctrineCustomerRepository::class)]
class Customer
{

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "datetimetz_immutable", nullable: false, options: ["default" => "CURRENT_TIMESTAMP"])]
    protected DateTimeImmutable $createdTime;

    #[Column(type: "string", enumType: CustomerStatus::class, options: ["default" => CustomerStatus::NEW->value])]
    protected CustomerStatus $status;

    #[Column(type: "string", length: 255, nullable: false)]
    protected string $name;

    #[Column(type: "string", length: 255, nullable: true)]
    protected ?string $email;

    #[Column(type: "string", length: 255, nullable: false)]
    protected string $phone;

    #[Column(type: "string", length: 255, nullable: true)]
    protected ?string $source;

    #[Column(type: "smallint", nullable: true)]
    protected ?int $rating;

    #[FetchableObjectList(targetEntity: GreetingAssignment::class, joinColumnName: "Customer_id", paginationRequired: false)]
    #[OneToMany(targetEntity: GreetingAssignment::class, mappedBy: "customer", fetch: "EXTRA_LAZY")]
    protected Collection $greetingAssignments;

    #[FetchableObjectList(targetEntity: FactFindingAssignment::class, joinColumnName: "Customer_id", paginationRequired: false)]
    #[OneToMany(targetEntity: FactFindingAssignment::class, mappedBy: "customer", fetch: "EXTRA_LAZY")]
    protected Collection $factFindingAssignments;

    #[FetchableObjectList(targetEntity: StrikingAssignment::class, joinColumnName: "Customer_id", paginationRequired: false)]
    #[OneToMany(targetEntity: StrikingAssignment::class, mappedBy: "customer", fetch: "EXTRA_LAZY")]
    protected Collection $strikingAssignments;

    //QUERY ONLY
    #[FetchableObject(targetEntity: City::class, joinColumnName: "City_id")]
    #[JoinColumn(name: "City_id", referencedColumnName: "id")]
    protected ?City $city;
    
    #[FetchableObjectList(targetEntity: VerificationReport::class, joinColumnName: "Customer_id", paginationRequired: false)]
    protected $verificationReports;
    
    public function getId(): string
    {
        return $this->id;
    }

    //
    protected function __construct()
    {
        
    }

    //
    public function assertHasNoActiveAssignment(): void
    {
        $criteria = Criteria::create()
                ->andWhere(Criteria::expr()->eq('status', CustomerAssignmentStatus::ACTIVE));
        $hasActiveAssignment = !$this->greetingAssignments->matching($criteria)->isEmpty() 
                || !$this->factFindingAssignments->matching($criteria)->isEmpty()
                || !$this->strikingAssignments->matching($criteria)->isEmpty();
        if ($hasActiveAssignment) {
            throw RegularException::forbidden('customer already being maintained');
        }
    }

    public function assertStatusEquals(CustomerStatus $status): void
    {
        if ($this->status !== $status) {
            throw RegularException::forbidden('unmatch customer status');
        }
    }
}
