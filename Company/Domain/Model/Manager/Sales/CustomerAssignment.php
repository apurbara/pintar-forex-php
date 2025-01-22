<?php

namespace Company\Domain\Model\Manager\Sales;

use Company\Domain\Model\Customer;
use Company\Domain\Model\CustomerJourney;
use Company\Domain\Model\Manager\Sales;
use Company\Domain\Model\Manager\Sales\CustomerAssignment\ClosingRequest;
use Company\Domain\Model\Manager\Sales\CustomerAssignment\CustomerAssignmentJourney;
use Company\Domain\Model\Manager\Sales\CustomerAssignment\RecycleRequest;
use Company\Domain\Model\Manager\Sales\CustomerAssignment\SalesActivitySchedule;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineCustomerAssignmentRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Resources\Event\ContainEventsInterface;
use Resources\Event\ContainEventsTrait;
use Resources\Exception\RegularException;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObjectList;
use Resources\Uuid;
use Shared\Domain\Enum\CustomerAssignmentStatus;
use Shared\Domain\Enum\ManagementApprovalStatus;
use Shared\Domain\Enum\SalesActivityScheduleStatus;

#[Entity(repositoryClass: DoctrineCustomerAssignmentRepository::class)]
class CustomerAssignment implements ContainEventsInterface
{

    use ContainEventsTrait;

    #[FetchableObject(targetEntity: Sales::class, joinColumnName: "Sales_id")]
    #[ManyToOne(targetEntity: Sales::class, inversedBy: "customerAssignments", fetch: "EXTRA_LAZY")]
    #[JoinColumn(name: "Sales_id", referencedColumnName: "id")]
    protected Sales $sales;

    #[FetchableObject(targetEntity: Customer::class, joinColumnName: "Customer_id")]
    #[ManyToOne(targetEntity: Customer::class, inversedBy: "customerAssignments", fetch: "EXTRA_LAZY")]
    #[JoinColumn(name: "Customer_id", referencedColumnName: "id")]
    protected Customer $customer;

    #[FetchableObject(targetEntity: CustomerJourney::class, joinColumnName: "CustomerJourney_id")]
    #[ManyToOne(targetEntity: CustomerJourney::class)]
    #[JoinColumn(name: "CustomerJourney_id", referencedColumnName: "id")]
    protected ?CustomerJourney $customerJourney;

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected DateTimeImmutable $createdTime;

    #[Column(type: "string", enumType: CustomerAssignmentStatus::class)]
    protected CustomerAssignmentStatus $status;

    #[FetchableObjectList(targetEntity: ClosingRequest::class, joinColumnName: "CustomerAssignment_id",
                paginationRequired: false)]
    #[OneToMany(targetEntity: ClosingRequest::class, mappedBy: "customerAssignment", fetch: "EXTRA_LAZY")]
    protected Collection $closingRequests;

    #[FetchableObjectList(targetEntity: RecycleRequest::class, joinColumnName: "CustomerAssignment_id",
                paginationRequired: false)]
    #[OneToMany(targetEntity: RecycleRequest::class, mappedBy: "customerAssignment", fetch: "EXTRA_LAZY")]
    protected Collection $recycleRequests;

    #[FetchableObjectList(targetEntity: SalesActivitySchedule::class, joinColumnName: "CustomerAssignment_id",
                paginationRequired: false)]
    #[OneToMany(targetEntity: SalesActivitySchedule::class, mappedBy: "customerAssignment", fetch: "EXTRA_LAZY")]
    protected Collection $salesActivitySchedules;

    #[FetchableObjectList(targetEntity: CustomerAssignmentJourney::class, joinColumnName: "CustomerAssignment_id",
                paginationRequired: false)]
    #[OneToMany(targetEntity: CustomerAssignmentJourney::class, mappedBy: "customerAssignment", cascade: ["persist"],
                fetch: "EXTRA_LAZY")]
    protected Collection $customerAssignmentJourneys;

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
        $this->createdTime = new \DateTimeImmutable();
        $this->status = CustomerAssignmentStatus::ACTIVE;
        //
        $this->sales->assertActive();
        $this->customer->assertHasNoActiveAssignment();
        $this->customerJourney?->assertActive();
        
        $this->customerAssignmentJourneys = new ArrayCollection();
        if (isset($customerJourney)) {
            $customerAssignmentJourney = new CustomerAssignmentJourney($this, $customerJourney, Uuid::generateUuid4());
            $this->customerAssignmentJourneys->add($customerAssignmentJourney);
        }
    }

    public function cancel(): void
    {
        $pendingRequestCriteria = Criteria::create()
                ->andWhere(Criteria::expr()->eq('status', ManagementApprovalStatus::WAITING_FOR_APPROVAL));
        $scheduledActivityCriteria = Criteria::create()
                ->andWhere(Criteria::expr()->eq('status', SalesActivityScheduleStatus::SCHEDULED));
        $hasPendingRequestOrSchedule = !$this->closingRequests->matching($pendingRequestCriteria)->isEmpty() || !$this->recycleRequests->matching($pendingRequestCriteria)->isEmpty() || !$this->salesActivitySchedules->matching($scheduledActivityCriteria)->isEmpty();
        if ($hasPendingRequestOrSchedule) {
            throw RegularException::forbidden('customer assignment has pending request or schedule');
        }
        $this->status = CustomerAssignmentStatus::CANCELLED;
    }

    public function cancelBySystem(): void
    {
        $this->status = CustomerAssignmentStatus::CANCELLED_BY_SYSTEM;

        $pendingRequestCriteria = Criteria::create()
                ->andWhere(Criteria::expr()->eq('status', ManagementApprovalStatus::WAITING_FOR_APPROVAL));

        foreach ($this->recycleRequests->matching($pendingRequestCriteria)->getIterator() as $pendingRecycleRequest) {
            $pendingRecycleRequest->cancelBySystem();
        }

        foreach ($this->closingRequests->matching($pendingRequestCriteria)->getIterator() as $pendingClosingRequest) {
            $pendingClosingRequest->cancelBySystem();
        }

        $scheduledActivityCriteria = Criteria::create()
                ->andWhere(Criteria::expr()->eq('status', SalesActivityScheduleStatus::SCHEDULED));
        foreach ($this->salesActivitySchedules->matching($scheduledActivityCriteria)->getIterator() as $scheduledActivity) {
            $scheduledActivity->cancelBySystem();
        }
    }
}
