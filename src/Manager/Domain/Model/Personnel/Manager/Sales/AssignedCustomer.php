<?php

namespace Manager\Domain\Model\Personnel\Manager\Sales;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Manager\Domain\Model\AreaStructure\Area\Customer;
use Manager\Domain\Model\CustomerJourney;
use Manager\Domain\Model\Personnel\Manager;
use Manager\Domain\Model\Personnel\Manager\Sales;
use Manager\Infrastructure\Persistence\Doctrine\Repository\DoctrineAssignedCustomerRepository;
use Resources\Event\ContainEventsInterface;
use Resources\Event\ContainEventsTrait;
use Resources\Exception\RegularException;
use SharedContext\Domain\Enum\CustomerAssignmentStatus;
use SharedContext\Domain\Enum\SalesType;
use SharedContext\Domain\Event\CustomerAssignedEvent;
use SharedContext\Domain\Event\InHouseSalesCustomerAssignmentRecycledEvent;

#[Entity(repositoryClass: DoctrineAssignedCustomerRepository::class)]
class AssignedCustomer implements ContainEventsInterface
{

    use ContainEventsTrait;

    #[ManyToOne(targetEntity: Sales::class, inversedBy: "assignedCustomers", fetch: "LAZY")]
    #[JoinColumn(name: "Sales_id", referencedColumnName: "id")]
    protected Sales $sales;

    #[ManyToOne(targetEntity: Customer::class)]
    #[JoinColumn(name: "Customer_id", referencedColumnName: "id")]
    protected Customer $customer;

    #[ManyToOne(targetEntity: CustomerJourney::class)]
    #[JoinColumn(name: "CustomerJourney_id", referencedColumnName: "id")]
    protected CustomerJourney $customerJourney;

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "string", enumType: CustomerAssignmentStatus::class)]
    protected CustomerAssignmentStatus $status;

    public function getStatus(): CustomerAssignmentStatus
    {
        return $this->status;
    }

    public function __construct(Sales $sales, Customer $customer, CustomerJourney $customerJourney, string $id)
    {
        $customerJourney->assertActive();
        
        $this->sales = $sales;
        $this->customer = $customer;
        $this->customerJourney = $customerJourney;
        $this->id = $id;
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
