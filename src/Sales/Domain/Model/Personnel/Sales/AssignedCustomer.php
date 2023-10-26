<?php

namespace Sales\Domain\Model\Personnel\Sales;

use Company\Domain\Model\Personnel\Manager\Sales as SalesInCompanyBC;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Resources\Attributes\FetchableEntity;
use Resources\Event\ContainEventsInterface;
use Resources\Event\ContainEventsTrait;
use Sales\Domain\Model\AreaStructure\Area\Customer;
use Sales\Domain\Model\Personnel\Sales;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineAssignedCustomerRepository;
use SharedContext\Domain\Event\CustomerAssignedEvent;

#[Entity(repositoryClass: DoctrineAssignedCustomerRepository::class)]
class AssignedCustomer implements ContainEventsInterface
{

    use ContainEventsTrait;

    #[FetchableEntity(targetEntity: SalesInCompanyBC::class, joinColumnName: "Sales_id")]
    #[ManyToOne(targetEntity: Sales::class)]
    #[JoinColumn(name: "Sales_id", referencedColumnName: "id")]
    protected Sales $sales;


    #[FetchableEntity(targetEntity: Customer::class, joinColumnName: "Customer_id")]
    #[ManyToOne(targetEntity: Customer::class, cascade: ["persist"])]
    #[JoinColumn(name: "Customer_id", referencedColumnName: "id")]
    protected Customer $customer;
    
    #[Id, Column(type: "guid")]
    protected string $id;
    
    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $disabled;
    
    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected DateTimeImmutable $createdTime;

    public function __construct(Sales $sales, Customer $customer, string $id)
    {
        $this->sales = $sales;
        $this->customer = $customer;
        $this->id = $id;
        $this->disabled = false;
        $this->createdTime = new DateTimeImmutable();

        $this->recordEvent(new CustomerAssignedEvent($this->id));
    }
}
