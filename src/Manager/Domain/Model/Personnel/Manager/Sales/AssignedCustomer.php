<?php

namespace Manager\Domain\Model\Personnel\Manager\Sales;

use Company\Domain\Model\CustomerJourney as CustomerJourneyInCompanyBC;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Manager\Domain\Model\AreaStructure\Area\Customer;
use Manager\Domain\Model\CustomerJourney;
use Manager\Domain\Model\Personnel\Manager;
use Manager\Domain\Model\Personnel\Manager\Sales;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer\ClosingRequest;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer\RecycleRequest;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer\SalesActivitySchedule;
use Manager\Infrastructure\Persistence\Doctrine\Repository\DoctrineAssignedCustomerRepository;
use Resources\Event\ContainEventsInterface;
use Resources\Event\ContainEventsTrait;
use Resources\Exception\RegularException;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObjectList;
use SharedContext\Domain\Enum\CustomerAssignmentStatus;
use SharedContext\Domain\Enum\SalesType;
use SharedContext\Domain\Event\CustomerAssignedEvent;
use SharedContext\Domain\Event\InHouseSalesCustomerAssignmentRecycledEvent;

#[Entity(repositoryClass: DoctrineAssignedCustomerRepository::class)]
class AssignedCustomer implements ContainEventsInterface
{

    use ContainEventsTrait;

    #[FetchableObject(targetEntity: Sales::class, joinColumnName: "Sales_id")]
    #[ManyToOne(targetEntity: Sales::class, inversedBy: "assignedCustomers", fetch: "EXTRA_LAZY")]
    #[JoinColumn(name: "Sales_id", referencedColumnName: "id")]
    protected Sales $sales;

    #[FetchableObject(targetEntity: Customer::class, joinColumnName: "Customer_id")]
    #[ManyToOne(targetEntity: Customer::class, inversedBy: "assignedCustomers", fetch: "EXTRA_LAZY")]
    #[JoinColumn(name: "Customer_id", referencedColumnName: "id")]
    protected Customer $customer;

    #[FetchableObject(targetEntity: CustomerJourneyInCompanyBC::class, joinColumnName: "CustomerJourney_id")]
    #[ManyToOne(targetEntity: CustomerJourney::class)]
    #[JoinColumn(name: "CustomerJourney_id", referencedColumnName: "id")]
    protected CustomerJourney $customerJourney;

    #[Id, Column(type: "guid")]
    protected string $id;
    
    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected DateTimeImmutable $createdTime;

    #[Column(type: "string", enumType: CustomerAssignmentStatus::class)]
    protected CustomerAssignmentStatus $status;

    //query purpose
    #[FetchableObjectList(targetEntity: ClosingRequest::class, joinColumnName: "AssignedCustomer_id",
                paginationRequired: false)]
    protected $closingRequests;

    #[FetchableObjectList(targetEntity: RecycleRequest::class, joinColumnName: "AssignedCustomer_id",
                paginationRequired: false)]
    protected $recycleRequests;

    #[FetchableObjectList(targetEntity: SalesActivitySchedule::class, joinColumnName: "AssignedCustomer_id",
                paginationRequired: false)]
    protected $salesActivitySchedules;

    public function getStatus(): CustomerAssignmentStatus
    {
        return $this->status;
    }

    public function __construct(Sales $sales, Customer $customer, CustomerJourney $customerJourney, string $id)
    {
        $customerJourney->assertActive();
        $customer->assertHasNoActiveAssignment();

        $this->sales = $sales;
        $this->customer = $customer;
        $this->customerJourney = $customerJourney;
        $this->id = $id;
        $this->createdTime = new DateTimeImmutable();
        $this->status = CustomerAssignmentStatus::ACTIVE;

        $event = new CustomerAssignedEvent($this->id);
        $this->recordEvent($event);
    }

    //
    protected function assertActiveAssignment()
    {
        if ($this->status !== CustomerAssignmentStatus::ACTIVE) {
            throw RegularException::forbidden('assignment already concluded');
        }
    }

    public function closeAssignment(): void
    {
        $this->assertActiveAssignment();
        $this->status = CustomerAssignmentStatus::GOOD_FUND;
    }

    public function recycleAssignment(): void
    {
        $this->assertActiveAssignment();
        $this->status = CustomerAssignmentStatus::RECYCLED;

        if ($this->sales->getType() === SalesType::IN_HOUSE) {
            $event = new InHouseSalesCustomerAssignmentRecycledEvent($this->customer->getId());
            $this->recordEvent($event);
        }
    }

    //
    public function isManageableByManager(Manager $manager): bool
    {
        return $this->sales->isManageableByManager($manager);
    }
}
