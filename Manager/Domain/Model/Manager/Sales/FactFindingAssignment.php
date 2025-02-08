<?php

namespace Manager\Domain\Model\Manager\Sales;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Manager\Domain\DependencyModel\Customer;
use Manager\Domain\Model\Manager;
use Manager\Domain\Model\Manager\Sales;
use Manager\Domain\Model\Manager\Sales\FactFindingAssignment\ClosingRequestByFactFinder;
use Manager\Infrastructure\Persistence\Doctrine\Repository\DoctrineFactFindingAssignmentRepository;
use Resources\Attributes\Composed;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObjectList;
use Shared\Domain\Enum\CustomerAssignmentStatus;
use Shared\Domain\Enum\CustomerStatus;
use Shared\Domain\Enum\SalesRole;

#[Entity(repositoryClass: DoctrineFactFindingAssignmentRepository::class)]
class FactFindingAssignment
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
    
    //
    #[FetchableObjectList(targetEntity: ClosingRequestByFactFinder::class, joinColumnName: "FactFindingAssignment_id",
                paginationRequired: false)]
    #[OneToMany(targetEntity: ClosingRequestByFactFinder::class, mappedBy: "factFindingAssignment", fetch: "EXTRA_LAZY")]
    protected Collection $closingRequestByFactFinders;

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
        $this->sales->assertRoleEquals(SalesRole::FACT_FINDER);
        $this->customer->assertHasNoActiveAssignment();
        $this->customer->assertStatusEquals(CustomerStatus::FACT_FINDING_REQUIRED);
    }
    
    //
    public function closeAssignment(): void
    {
        $this->customerAssignment->completeAssignment();
        $this->customer->updateStatus(CustomerStatus::TRANSACTION_BY_FACT_FINDER);
        $this->status = CustomerAssignmentStatus::COMPLETED;
    }
    
    public function belongsToManager(Manager $manager): bool
    {
        return $this->sales->belongsToManager($manager);
    }
}
