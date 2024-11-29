<?php

namespace Sales\Domain\Model\Sales;

use Company\Domain\Model\Manager\Sales as Sales2;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Resources\Attributes\Composed;
use Resources\Event\ContainEventsInterface;
use Resources\Event\ContainEventsTrait;
use Resources\Exception\RegularException;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;
use Resources\Infrastructure\GraphQL\Attributes\IncludeAsInput;
use Sales\Domain\DependencyModel\Customer;
use Sales\Domain\DependencyModel\CustomerData;
use Sales\Domain\DependencyModel\Province\City;
use Sales\Domain\DependencyModel\SalesActivity;
use Sales\Domain\Event\CustomerValidated;
use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule\SalesActivityReport;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule\SalesActivityReportData;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivityScheduleData;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineGreetingAssignmentRepository;
use Shared\Domain\Enum\CustomerAssignmentStatus;
use Shared\Domain\Enum\GreetingResult;

#[Entity(repositoryClass: DoctrineGreetingAssignmentRepository::class)]
class GreetingAssignment implements ContainEventsInterface, ContainCustomerAssignmentInterface
{

    use ContainEventsTrait;

    #[FetchableObject(targetEntity: Sales2::class, joinColumnName: "Customer_id")]
    #[ManyToOne(targetEntity: Sales::class, fetch: "EXTRA_LAZY")]
    #[JoinColumn(name: "Sales_id", referencedColumnName: "id")]
    protected Sales $sales;

    #[IncludeAsInput(targetEntity: Customer::class)]
    #[FetchableObject(targetEntity: Customer::class, joinColumnName: "Customer_id")]
    #[ManyToOne(targetEntity: Customer::class, fetch: "EXTRA_LAZY")]
    #[JoinColumn(name: "Customer_id", referencedColumnName: "id")]
    protected Customer $customer;

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "string", enumType: CustomerAssignmentStatus::class)]
    protected CustomerAssignmentStatus $status;
    
    #[Column(type: "string", enumType: GreetingResult::class, nullable: true)]
    protected GreetingResult $greetingResult;

    #[Composed(class: CustomerAssignment::class)]
    #[OneToOne(targetEntity: CustomerAssignment::class, inversedBy: "greetingAssignment", cascade: ["persist"])]
    #[JoinColumn(name: "CustomerAssignment_id", referencedColumnName: "id")]
    protected CustomerAssignment $customerAssignment;

    protected function __construct()
    {
        
    }

    //
    private function assertActive(): void
    {
        if ($this->status !== CustomerAssignmentStatus::ACTIVE) {
            throw RegularException::forbidden('inactive assignment');
        }
    }
    
    public function isBelongsToSales(Sales $sales): bool
    {
        return $this->sales === $sales;
    }

    public function assertBelongsToSales(Sales $sales): void
    {
        if ($this->sales !== $sales) {
            throw RegularException::forbidden('unmanaged assignment');
        }
    }

    //
    public function updateCustomer(CustomerData $customerData, ?City $city): void
    {
        $this->assertActive();
        $this->customer->update($city, $customerData);
    }
    
    public function updateCustomerRating(int $rating): void
    {
        $this->assertActive();
        $this->customer->updateRating($rating);
    }

    public function validateCustomer(): void
    {
        $this->assertActive();
        $this->customer->validate();
        $this->status = CustomerAssignmentStatus::COMPLETED;
        $this->greetingResult = GreetingResult::VALIDATED;
        
        $event = new CustomerValidated($this->id);
        $this->recordEvent($event);
    }

    public function recycleCustomer(): void
    {
        $this->assertActive();
        $this->customer->recycle();
        $this->status = CustomerAssignmentStatus::COMPLETED;
        $this->greetingResult = GreetingResult::RECYCLED;
    }

    public function submitNonScheduledSalesActivityReport(
            SalesActivity $salesActivity, string $reportId, SalesActivityReportData $salesActivityReportData): SalesActivityReport
    {
        $this->assertActive();
        return $this->customerAssignment
                        ->submitNonScheduledSalesActivityReport($salesActivity, $reportId, $salesActivityReportData);
    }

    public function submitSalesActivitySchedule(
            SalesActivity $salesActivity, string $scheduleId, SalesActivityScheduleData $salesActivityScheduleData): SalesActivitySchedule
    {
        $this->assertActive();
        return new SalesActivitySchedule($this->customerAssignment, $salesActivity, $scheduleId,
                $salesActivityScheduleData);
    }
}
