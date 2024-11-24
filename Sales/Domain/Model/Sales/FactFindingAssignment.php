<?php

namespace Sales\Domain\Model\Sales;

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
use Sales\Domain\DependencyModel\Customer;
use Sales\Domain\DependencyModel\Customer\VerificationReportData;
use Sales\Domain\DependencyModel\CustomerVerification;
use Sales\Domain\DependencyModel\SalesActivity;
use Sales\Domain\Event\CustomerVerified;
use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule\SalesActivityReport;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule\SalesActivityReportData;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivityScheduleData;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineFactFindingAssignmentRepository;
use Shared\Domain\Enum\CustomerAssignmentStatus;

#[Entity(repositoryClass: DoctrineFactFindingAssignmentRepository::class)]
class FactFindingAssignment implements ContainEventsInterface, ContainCustomerAssignmentInterface
{
    use ContainEventsTrait;

    #[ManyToOne(targetEntity: Sales::class, fetch: "EXTRA_LAZY")]
    #[JoinColumn(name: "Sales_id", referencedColumnName: "id")]
    protected Sales $sales;

    #[FetchableObject(targetEntity: Customer::class, joinColumnName: "Customer_id")]
    #[ManyToOne(targetEntity: Customer::class, fetch: "EXTRA_LAZY")]
    #[JoinColumn(name: "Customer_id", referencedColumnName: "id")]
    protected Customer $customer;

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "string", enumType: CustomerAssignmentStatus::class)]
    protected CustomerAssignmentStatus $status;

    #[Composed(class: CustomerAssignment::class)]
    #[OneToOne(targetEntity: CustomerAssignment::class, inversedBy: "factFindingAssignment", cascade: ["persist"])]
    #[JoinColumn(name: "CustomerAssignment_id", referencedColumnName: "id")]
    protected CustomerAssignment $customerAssignment;

    protected function __construct()
    {
        
    }

    //
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

    private function assertActive(): void
    {
        if ($this->status !== CustomerAssignmentStatus::ACTIVE) {
            throw RegularException::forbidden('inactive assignment');
        }
    }

    //
    public function SubmitCustomerVerificationReport(
            CustomerVerification $customerVerification, VerificationReportData $verificationReportData): void
    {
        $this->assertActive();
        $this->customer->submitVerificationReport($customerVerification, $verificationReportData);
    }

    public function updateCustomerRating(int $rating): void
    {
        $this->assertActive();
        $this->customer->updateRating($rating);
    }

    public function markCustomerVerified(array $allActiveCustomerVerifications): void
    {
        $this->assertActive();
        $this->customer->markVerificationComplete($allActiveCustomerVerifications);
        $this->status = CustomerAssignmentStatus::COMPLETED;
        
        $event = new CustomerVerified($this->id);
        $this->recordEvent($event);
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
        return new SalesActivitySchedule(
                $this->customerAssignment, $salesActivity, $scheduleId, $salesActivityScheduleData);
    }
}
