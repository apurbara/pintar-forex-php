<?php

namespace Company\Domain\Model\Manager\Sales;

use Company\Domain\Model\Customer;
use Company\Domain\Model\Manager\Sales;
use Company\Domain\Service\SalesFinderService;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineGreetingAssignmentRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Resources\Attributes\Composed;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;
use Shared\Domain\Enum\CustomerAssignmentStatus;
use Shared\Domain\Enum\CustomerStatus;
use Shared\Domain\Enum\SalesRole;

#[Entity(repositoryClass: DoctrineGreetingAssignmentRepository::class)]
class GreetingAssignment
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

    public function getStatus(): CustomerAssignmentStatus
    {
        return $this->status;
    }

    public function __construct(Sales $sales, Customer $customer, string $id)
    {
        $this->sales = $sales;
        $this->customer = $customer;
        $this->id = $id;
        $this->status = CustomerAssignmentStatus::ACTIVE;
        $this->customerAssignment = new CustomerAssignment($id);
        //
        $this->sales->assertActive();
        $this->sales->assertRoleEquals(SalesRole::GREETER);
        $this->customer->assertHasNoActiveAssignment();
        $this->customer->assertStatusIn([CustomerStatus::NEW, CustomerStatus::RECYCLED]);
    }

    public function cancel(): void
    {
        $this->status = CustomerAssignmentStatus::CANCELLED;
        $this->customerAssignment->cancelAllActiveSchedule();
    }

    public function cancelBySystem(): void
    {
        $this->status = CustomerAssignmentStatus::CANCELLED_BY_SYSTEM;
        $this->customerAssignment->cancelAllActiveSchedule();
    }

    //
    public function handoverCustomerToFactFinder(SalesFinderService $salesFinderService, string $factFindingAssignmentId): ?FactFindingAssignment
    {
        $managerId = $this->sales->getManagerId();
        $factFinder = $salesFinderService->findLeastOccupiedFactFinderBelongsToManager($managerId);
        return isset($factFinder) ? new FactFindingAssignment($factFinder, $this->customer, $factFindingAssignmentId) : null;
    }
}
