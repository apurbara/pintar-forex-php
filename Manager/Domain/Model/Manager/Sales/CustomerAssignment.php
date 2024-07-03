<?php

namespace Manager\Domain\Model\Manager\Sales;

use Company\Domain\Model\Customer as FetchableCustomerFromCompanyBC;
use Company\Domain\Model\CustomerJourney as FetchableCustomerJourneyFromCompanyBC;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Manager\Domain\DependencyModel\Customer;
use Manager\Domain\DependencyModel\CustomerJourney;
use Manager\Domain\Model\Manager;
use Manager\Domain\Model\Manager\Sales;
use Manager\Domain\Model\Manager\Sales\CustomerAssignment\ClosingRequest;
use Manager\Domain\Model\Manager\Sales\CustomerAssignment\RecycleRequest;
use Manager\Domain\Model\Manager\Sales\CustomerAssignment\SalesActivitySchedule;
use Manager\Infrastructure\Persistence\Doctrine\Repository\DoctrineCustomerAssignmentRepository;
use Resources\Event\ContainEventsInterface;
use Resources\Event\ContainEventsTrait;
use Resources\Exception\RegularException;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObjectList;
use SharedContext\Domain\Enum\CustomerAssignmentStatus;
use SharedContext\Domain\Event\CustomerAssignedEvent;
use SharedContext\Domain\Event\InHouseSalesCustomerAssignmentRecycledEvent;

#[Entity(repositoryClass: DoctrineCustomerAssignmentRepository::class)]
class CustomerAssignment implements ContainEventsInterface
{

    use ContainEventsTrait;

    #[FetchableObject(targetEntity: Sales::class, joinColumnName: "Sales_id")]
    #[ManyToOne(targetEntity: Sales::class, inversedBy: "customerAssignments", fetch: "EXTRA_LAZY")]
    #[JoinColumn(name: "Sales_id", referencedColumnName: "id")]
    protected Sales $sales;

    #[FetchableObject(targetEntity: FetchableCustomerFromCompanyBC::class, joinColumnName: "Customer_id")]
    #[ManyToOne(targetEntity: Customer::class, inversedBy: "customerAssignments", fetch: "EXTRA_LAZY")]
    #[JoinColumn(name: "Customer_id", referencedColumnName: "id")]
    protected Customer $customer;

    #[FetchableObject(targetEntity: FetchableCustomerJourneyFromCompanyBC::class, joinColumnName: "CustomerJourney_id")]
    #[ManyToOne(targetEntity: CustomerJourney::class)]
    #[JoinColumn(name: "CustomerJourney_id", referencedColumnName: "id")]
    protected ?CustomerJourney $customerJourney;

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected DateTimeImmutable $createdTime;

    #[Column(type: "string", enumType: CustomerAssignmentStatus::class)]
    protected CustomerAssignmentStatus $status;

    //query purpose
    #[FetchableObjectList(targetEntity: ClosingRequest::class, joinColumnName: "CustomerAssignment_id",
                paginationRequired: false)]
    protected $closingRequests;

    #[FetchableObjectList(targetEntity: RecycleRequest::class, joinColumnName: "CustomerAssignment_id",
                paginationRequired: false)]
    protected $recycleRequests;

    #[FetchableObjectList(targetEntity: SalesActivitySchedule::class, joinColumnName: "CustomerAssignment_id",
                paginationRequired: false)]
    protected $salesActivitySchedules;

    public function getStatus(): CustomerAssignmentStatus
    {
        return $this->status;
    }

    public function __construct(Sales $sales, Customer $customer, ?CustomerJourney $customerJourney, string $id)
    {

        $this->sales = $sales;
        $this->customer = $customer;
        $this->customerJourney = $customerJourney;
        $this->id = $id;
        $this->createdTime = new DateTimeImmutable();
        $this->status = CustomerAssignmentStatus::ACTIVE;
        //
        $event = new CustomerAssignedEvent($this->id);
        $this->recordEvent($event);
        //
        $this->sales->assertActive();
        $this->customerJourney?->assertActive();
        $this->customer->assertActive();
        $this->customer->assertHasNoActiveAssignment();
    }

    public function closeAssignment(): void
    {
        $this->assertActiveAssignment();
        $this->status = CustomerAssignmentStatus::GOOD_FUND;
    }

    public function recycle(): void
    {
        $this->assertActiveAssignment();
        $this->status = CustomerAssignmentStatus::RECYCLED;

        if ($this->sales->isInHouseSales()) {
            $event = new InHouseSalesCustomerAssignmentRecycledEvent($this->customer->getId());
            $this->recordEvent($event);
        }
    }

    public function cancel(): void
    {
        $this->status = CustomerAssignmentStatus::CANCELLED;
    }

    //
    protected function assertActiveAssignment()
    {
        if ($this->status !== CustomerAssignmentStatus::ACTIVE) {
            throw RegularException::forbidden('assignment already concluded');
        }
    }

    public function belongsToManager(Manager $manager): bool
    {
        return $this->sales->belongsToManager($manager);
    }

    public function assertBelongsToManager(Manager $manager): void
    {
        if (!$this->belongsToManager($manager)) {
            throw RegularException::forbidden('customer assignment does not belongs to manager');
        }
    }
}
