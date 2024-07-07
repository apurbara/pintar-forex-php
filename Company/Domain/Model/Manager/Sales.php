<?php

namespace Company\Domain\Model\Manager;

use Company\Domain\Model\CompanyUser;
use Company\Domain\Model\Customer;
use Company\Domain\Model\CustomerJourney;
use Company\Domain\Model\Manager;
use Company\Domain\Model\Manager\Sales\CustomerAssignment;
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
use Resources\Event\ContainEventsInterface;
use Resources\Event\ContainEventsTrait;
use Resources\Exception\RegularException;
use Resources\Infrastructure\GraphQL\Attributes\ExcludeFromInput;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObjectList;
use Shared\Domain\Enum\CustomerAssignmentStatus;
use Shared\Domain\Enum\SalesType;
use Shared\Domain\Event\MultipleCustomerAssignmentReceivedBySales;
use Shared\Domain\ValueObject\AccountInfo;

#[Entity(repositoryClass: DoctrineSalesRepository::class)]
class Sales implements CompanyUser, ContainEventsInterface
{
    use ContainEventsTrait;

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

    #[Column(type: "string", enumType: SalesType::class)]
    protected SalesType $type;

    #[FetchableObjectList(targetEntity: CustomerAssignment::class, joinColumnName: "Sales_id", paginationRequired: true)]
    #[OneToMany(targetEntity: CustomerAssignment::class, mappedBy: "sales", fetch: "EXTRA_LAZY")]
    protected Collection $customerAssignments;

    public function __construct(Manager $manager, ?City $city, string $id, SalesData $data)
    {
        $this->manager = $manager;
        $this->city = $city;
        $this->id = $id;
        $this->contractTerminated = false;
        $this->createdTime = new DateTimeImmutable();
        $this->contractTerminatedTime = null;
        $this->type = SalesType::from($data->type);
        $this->accountInfo = new AccountInfo($data->accountInfoData);

        //
        $this->manager->assertActive();
        $this->city?->assertActive();
    }

    public function terminateContract(): void
    {
        if ($this->contractTerminated) {
            throw RegularException::forbidden('contract already terminated');
        }

        $this->contractTerminated = true;
        $this->contractTerminatedTime = new DateTimeImmutable();

        $criteria = Criteria::create()
                ->andWhere(Criteria::expr()->eq('status', CustomerAssignmentStatus::ACTIVE));
        foreach ($this->customerAssignments->matching($criteria)->getIterator() as $customerAssignment) {
            $customerAssignment->cancelBySystem();
        }
    }
    
    //
    public function assertActive(): void
    {
        if ($this->contractTerminated) {
            throw RegularException::forbidden('inactive sales');
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
        $this->activeCustomerAssignmentCount ??= $this->customerAssignments->matching($criteria)->count();
        return $this->activeCustomerAssignmentCount;
    }

    protected ?MultipleCustomerAssignmentReceivedBySales $multipleCustomerAssignmentReceivedBySalesEvent;

    public function receiveCustomerAssignment(string $id, Customer $customer, ?CustomerJourney $customerJourney): ?CustomerAssignment
    {

        if (empty($this->multipleCustomerAssignmentReceivedBySalesEvent)) {
            $this->multipleCustomerAssignmentReceivedBySalesEvent = new MultipleCustomerAssignmentReceivedBySales($this->id);
            $this->recordEvent($this->multipleCustomerAssignmentReceivedBySalesEvent);
        }

        if ($customer->hasActiveAssignment()) {
            return null;
        }
        $customerAssignment = new CustomerAssignment($this, $customer, $customerJourney, $id);
        $this->multipleCustomerAssignmentReceivedBySalesEvent->addCustomerAssignmentId($id);
        $this->activeCustomerAssignmentCount++;
        return $customerAssignment;
    }
}
