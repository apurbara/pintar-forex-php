<?php

namespace Company\Domain\Model\Manager;

use Company\Domain\Model\CompanyUser;
use Company\Domain\Model\Customer;
use Company\Domain\Model\Manager;
use Company\Domain\Model\Manager\Sales\FactFindingAssignment;
use Company\Domain\Model\Manager\Sales\GreetingAssignment;
use Company\Domain\Model\Manager\Sales\StrikingAssignment;
use Company\Domain\Model\Province\City;
use Company\Domain\Task\TaskInCompany;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesRepository;
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
use Resources\Exception\RegularException;
use Resources\Infrastructure\GraphQL\Attributes\ExcludeFromInput;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObjectList;
use Shared\Domain\Enum\CustomerAssignmentStatus;
use Shared\Domain\Enum\SalesRole;
use Shared\Domain\ValueObject\AccountInfo;

#[Entity(repositoryClass: DoctrineSalesRepository::class)]
class Sales implements CompanyUser
{

    #[FetchableObject(targetEntity: Manager::class, joinColumnName: "Manager_id")]
    #[ManyToOne(targetEntity: Manager::class)]
    #[JoinColumn(name: "Manager_id", referencedColumnName: "id")]
    protected Manager $manager;

    #[FetchableObject(targetEntity: City::class, joinColumnName: "City_id")]
    #[ManyToOne(targetEntity: City::class)]
    #[JoinColumn(name: "City_id", referencedColumnName: "id")]
    protected ?City $city;

    #[Id, Column(type: "guid")]
    protected string $id;

    #[ExcludeFromInput]
    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $contractTerminated;

    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected DateTimeImmutable $createdTime;

    #[ExcludeFromInput]
    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected ?DateTimeImmutable $contractTerminatedTime;

    #[Embedded(class: AccountInfo::class, columnPrefix: false)]
    protected AccountInfo $accountInfo;

    #[Column(type: "string", enumType: SalesRole::class)]
    protected SalesRole $role;

    #[FetchableObjectList(targetEntity: GreetingAssignment::class, joinColumnName: "Sales_id", paginationRequired: true)]
    #[OneToMany(targetEntity: GreetingAssignment::class, mappedBy: "sales", fetch: "EXTRA_LAZY", cascade: ["persist"])]
    protected Collection $greetingAssignments;

    #[FetchableObjectList(targetEntity: FactFindingAssignment::class, joinColumnName: "Sales_id",
                paginationRequired: true)]
    #[OneToMany(targetEntity: FactFindingAssignment::class, mappedBy: "sales", fetch: "EXTRA_LAZY", cascade: ["persist"])]
    protected Collection $factFindingAssignments;

    #[FetchableObjectList(targetEntity: StrikingAssignment::class, joinColumnName: "Sales_id", paginationRequired: true)]
    #[OneToMany(targetEntity: StrikingAssignment::class, mappedBy: "sales", fetch: "EXTRA_LAZY", cascade: ["persist"])]
    protected Collection $strikingAssignments;

    public function getManagerId(): string
    {
        return $this->manager->getId();
    }
    
    public function __construct(Manager $manager, ?City $city, string $id, SalesData $data)
    {
        $this->manager = $manager;
        $this->city = $city;
        $this->id = $id;
        $this->contractTerminated = false;
        $this->createdTime = new DateTimeImmutable();
        $this->contractTerminatedTime = null;
        $this->role = SalesRole::from($data->role);
        $this->accountInfo = new AccountInfo($data->accountInfoData);

        //
        $this->manager->assertActive();
        $this->city?->assertActive();
    }

    private function cancelAllActiveAssignments(): void
    {
        $criteria = Criteria::create()
                ->andWhere(Criteria::expr()->eq('status', CustomerAssignmentStatus::ACTIVE));
        foreach ($this->greetingAssignments->matching($criteria)->getIterator() as $greetingAssignment) {
            $greetingAssignment->cancelBySystem();
        }
        foreach ($this->factFindingAssignments->matching($criteria)->getIterator() as $factFindingAssignment) {
            $factFindingAssignment->cancelBySystem();
        }
        foreach ($this->strikingAssignments->matching($criteria)->getIterator() as $strikingAssignment) {
            $strikingAssignment->cancelBySystem();
        }
    }

    public function update(Manager $manager, ?City $city, SalesData $data): void
    {
        $this->manager = $manager;
        $this->city = $city;
        //
        $this->manager->assertActive();
        $this->city?->assertActive();
        
        if ($this->role != SalesRole::from($data->role)) {
            $this->cancelAllActiveAssignments();
            $this->role = SalesRole::from($data->role);
        }
    }

    public function terminateContract(): void
    {
        if ($this->contractTerminated) {
            throw RegularException::forbidden('contract already terminated');
        }

        $this->contractTerminated = true;
        $this->contractTerminatedTime = new DateTimeImmutable();

        $this->cancelAllActiveAssignments();
    }
    
    public function receiveFactFindingAssignment(Customer $customer): void
    {
//        $factFindingAssignment = new FactFindingAssignment($this, $customer, Uuid::generateUuid4());
//        $this->factFindingAssignments->add($factFindingAssignment);
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
        if ($this->role != $role) {
            throw RegularException::forbidden('unmatch role');
        }
    }

    //
    private function executeSalesTaskInCompany(SalesTaskInCompany $task, $payload): void
    {
        $task->executeInCompany($payload);
    }

    public function executeTaskInCompany(TaskInCompany $task, $payload): void
    {
        if ($this->contractTerminated) {
            throw RegularException::forbidden('only active sales can make this request');
        }
        $this->executeSalesTaskInCompany($task, $payload);
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
