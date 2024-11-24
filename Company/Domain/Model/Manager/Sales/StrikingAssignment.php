<?php

namespace Company\Domain\Model\Manager\Sales;

use Company\Domain\Model\Customer;
use Company\Domain\Model\CustomerJourney;
use Company\Domain\Model\Manager\Sales;
use Company\Domain\Model\Manager\Sales\StrikingAssignment\ClosingRequest;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineStrikingAssignmentRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Resources\Attributes\Composed;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObjectList;
use Shared\Domain\Enum\CustomerAssignmentStatus;
use Shared\Domain\Enum\CustomerStatus;
use Shared\Domain\Enum\ManagementApprovalStatus;
use Shared\Domain\Enum\SalesRole;

#[Entity(repositoryClass: DoctrineStrikingAssignmentRepository::class)]
class StrikingAssignment
{

    #[FetchableObject(targetEntity: Sales::class, joinColumnName: "Sales_id")]
    #[ManyToOne(targetEntity: Sales::class, inversedBy: "customerAssignments", fetch: "EXTRA_LAZY")]
    #[JoinColumn(name: "Sales_id", referencedColumnName: "id")]
    protected Sales $sales;

    #[FetchableObject(targetEntity: Customer::class, joinColumnName: "Customer_id")]
    #[ManyToOne(targetEntity: Customer::class, inversedBy: "customerAssignments", fetch: "EXTRA_LAZY")]
    #[JoinColumn(name: "Customer_id", referencedColumnName: "id")]
    protected Customer $customer;

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "string", enumType: CustomerAssignmentStatus::class)]
    protected CustomerAssignmentStatus $status;

    #[Composed(class: CustomerAssignment::class)]
    #[OneToOne(targetEntity: CustomerAssignment::class, cascade: ["persist"])]
    #[JoinColumn(name: "CustomerAssignment_id", referencedColumnName: "id")]
    protected CustomerAssignment $customerAssignment;

    #[FetchableObjectList(targetEntity: ClosingRequest::class, joinColumnName: "CustomerAssignment_id",
                paginationRequired: false)]
    #[OneToMany(targetEntity: ClosingRequest::class, mappedBy: "customerAssignment", fetch: "EXTRA_LAZY")]
    protected Collection $closingRequests;

    #[FetchableObject(targetEntity: CustomerJourney::class, joinColumnName: "CustomerJourney_id")]
    #[ManyToOne(targetEntity: CustomerJourney::class)]
    #[JoinColumn(name: "CustomerJourney_id", referencedColumnName: "id")]
    protected ?CustomerJourney $customerJourney;

    public function getStatus(): CustomerAssignmentStatus
    {
        return $this->status;
    }

    public function __construct(Sales $sales, Customer $customer, string $id, ?CustomerJourney $customerJourney)
    {
        $this->sales = $sales;
        $this->customer = $customer;
        $this->id = $id;
        $this->status = CustomerAssignmentStatus::ACTIVE;
        $this->customerAssignment = new CustomerAssignment($id);
        $this->customerJourney = $customerJourney;
        //
        $this->sales->assertActive();
        $this->sales->assertRoleEquals(SalesRole::STRIKER);
        $this->customer->assertHasNoActiveAssignment();
        $this->customer->assertStatusEquals(CustomerStatus::STRIKING_REQUIRED);
        $this->customerJourney?->assertActive();
    }

    private function cancelPendingClosingRequests()
    {
        $pendingRequestCriteria = Criteria::create()
                ->andWhere(Criteria::expr()->eq('status', ManagementApprovalStatus::WAITING_FOR_APPROVAL));
        foreach ($this->closingRequests->matching($pendingRequestCriteria)->getIterator() as $pendingClosingRequest) {
            $pendingClosingRequest->cancelBySystem();
        }
    }

    public function cancel(): void
    {
        $this->cancelPendingClosingRequests();
        $this->customerAssignment->cancelAllActiveSchedule();
        $this->status = CustomerAssignmentStatus::CANCELLED;
    }

    public function cancelBySystem(): void
    {
        $this->cancelPendingClosingRequests();
        $this->customerAssignment->cancelAllActiveSchedule();
        $this->status = CustomerAssignmentStatus::CANCELLED_BY_SYSTEM;
    }
}
