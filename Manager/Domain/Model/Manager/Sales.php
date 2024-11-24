<?php

namespace Manager\Domain\Model\Manager;

use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Manager\Domain\Model\Manager;
use Manager\Domain\Model\Manager\Sales\FactFindingAssignment;
use Manager\Domain\Model\Manager\Sales\GreetingAssignment;
use Manager\Domain\Model\Manager\Sales\StrikingAssignment;
use Manager\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesRepository;
use Resources\Event\ContainEventsInterface;
use Resources\Event\ContainEventsTrait;
use Resources\Exception\RegularException;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObjectList;
use Shared\Domain\Enum\CustomerAssignmentStatus;
use Shared\Domain\Enum\SalesRole;
use Shared\Domain\ValueObject\AccountInfo;

#[Entity(repositoryClass: DoctrineSalesRepository::class)]
class Sales implements ContainEventsInterface
{

    use ContainEventsTrait;

    #[FetchableObject(targetEntity: Manager::class, joinColumnName: "Manager_id")]
    #[ManyToOne(targetEntity: Manager::class, fetch: "EXTRA_LAZY")]
    #[JoinColumn(name: "Manager_id", referencedColumnName: "id")]
    protected Manager $manager;

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $contractTerminated;

    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected DateTimeImmutable $createdTime;

    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected ?DateTimeImmutable $contractTerminatedTime;

    #[Embedded(class: AccountInfo::class, columnPrefix: false)]
    protected AccountInfo $accountInfo;
    
    #[Column(type: "string", enumType: SalesRole::class)]
    protected SalesRole $role;
    
    #[FetchableObjectList(targetEntity: GreetingAssignment::class, joinColumnName: "Sales_id", paginationRequired: true)]
    #[OneToMany(targetEntity: GreetingAssignment::class, mappedBy: "sales", fetch: "EXTRA_LAZY")]
    protected Collection $greetingAssignments;

    #[FetchableObjectList(targetEntity: FactFindingAssignment::class, joinColumnName: "Sales_id",
                paginationRequired: true)]
    #[OneToMany(targetEntity: FactFindingAssignment::class, mappedBy: "sales", fetch: "EXTRA_LAZY")]
    protected Collection $factFindingAssignments;

    #[FetchableObjectList(targetEntity: StrikingAssignment::class, joinColumnName: "Sales_id", paginationRequired: true)]
    #[OneToMany(targetEntity: StrikingAssignment::class, mappedBy: "sales", fetch: "EXTRA_LAZY")]
    protected Collection $strikingAssignments;

    protected function __construct()
    {
        
    }

    //
    public function assertActive(): void
    {
        if ($this->contractTerminated) {
            throw RegularException::forbidden('inactive sales');
        }
    }
    
    public function assertRoleEquals(SalesRole $role): void
    {
        if ($this->role !== $role) {
            throw RegularException::forbidden('unmatch role');
        }
    }

    public function belongsToManager(Manager $manager): bool
    {
        return $this->manager === $manager;
    }

    public function assertBelongsToManager(Manager $manager): void
    {
        if (!$this->belongsToManager($manager)) {
            throw RegularException::forbidden('sales does not belongs to manager');
        }
    }

    //
    protected ?int $activeCustomerAssignmentCount = null;

    public function calculateActiveCustomerAssignmentsCount(): ?int
    {
        $criteria = Criteria::create()
                ->andWhere(Criteria::expr()->eq('status', CustomerAssignmentStatus::ACTIVE));
        $this->activeCustomerAssignmentCount ??= match ($this->role) {
            SalesRole::GREETER => $this->greetingAssignments->matching($criteria)->count(),
            SalesRole::FACT_FINDER => $this->factFindingAssignments->matching($criteria)->count(),
            SalesRole::STRIKER => $this->strikingAssignments->matching($criteria)->count(),
        };
        return $this->activeCustomerAssignmentCount;
    }
    
    public function incrementActiveAssignmentCount(): void
    {
        $this->activeCustomerAssignmentCount++;
    }
}
