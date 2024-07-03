<?php

namespace Company\Domain\Model\Manager\Sales;

use Company\Domain\Model\Customer;
use Company\Domain\Model\CustomerJourney;
use Company\Domain\Model\Manager\Sales;
use Company\Domain\Model\Manager\Sales\CustomerAssignment\ClosingRequest;
use Company\Domain\Model\Manager\Sales\CustomerAssignment\RecycleRequest;
use Company\Domain\Model\Manager\Sales\CustomerAssignment\SalesActivitySchedule;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineCustomerAssignmentRepository;
use DateTimeImmutable;
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
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObjectList;
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

    public function getStatus(): CustomerAssignmentStatus
    {
        return $this->status;
    }

    protected function __construct()
    {
        
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
