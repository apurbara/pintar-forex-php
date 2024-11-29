<?php

namespace Sales\Domain\Model\Sales;

use Company\Domain\Model\Customer as Customer2;
use Company\Domain\Model\CustomerJourney as CustomerJourney2;
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
use Resources\Exception\RegularException;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObjectList;
use Sales\Domain\DependencyModel\Customer;
use Sales\Domain\DependencyModel\CustomerJourney;
use Sales\Domain\DependencyModel\SalesActivity;
use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule\SalesActivityReport;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule\SalesActivityReportData;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivityScheduleData;
use Sales\Domain\Model\Sales\StrikingAssignment\ClosingRequest;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineStrikingAssignmentRepository;
use Shared\Domain\Enum\CustomerAssignmentStatus;
use Shared\Domain\Enum\ManagementApprovalStatus;

#[Entity(repositoryClass: DoctrineStrikingAssignmentRepository::class)]
class StrikingAssignment implements ContainCustomerAssignmentInterface
{

    #[ManyToOne(targetEntity: Sales::class, fetch: "EXTRA_LAZY")]
    #[JoinColumn(name: "Sales_id", referencedColumnName: "id")]
    protected Sales $sales;

    #[FetchableObject(targetEntity: Customer2::class, joinColumnName: "Customer_id")]
    #[ManyToOne(targetEntity: Customer::class, fetch: "EXTRA_LAZY")]
    #[JoinColumn(name: "Customer_id", referencedColumnName: "id")]
    protected Customer $customer;

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "string", enumType: CustomerAssignmentStatus::class)]
    protected CustomerAssignmentStatus $status;

    #[Composed(class: CustomerAssignment::class)]
    #[OneToOne(targetEntity: CustomerAssignment::class, inversedBy: "strikingAssignment", cascade: ["persist"])]
    #[JoinColumn(name: "CustomerAssignment_id", referencedColumnName: "id")]
    protected CustomerAssignment $customerAssignment;

    #[FetchableObjectList(targetEntity: ClosingRequest::class, joinColumnName: "StrikingAssignment_id",
                paginationRequired: false)]
    #[OneToMany(targetEntity: ClosingRequest::class, mappedBy: "customerAssignment", fetch: "EXTRA_LAZY")]
    protected Collection $closingRequests;

    #[FetchableObject(targetEntity: CustomerJourney2::class, joinColumnName: "CustomerJourney_id")]
    #[ManyToOne(targetEntity: CustomerJourney::class)]
    #[JoinColumn(name: "CustomerJourney_id", referencedColumnName: "id")]
    protected ?CustomerJourney $customerJourney;

    protected function __construct()
    {
        
    }

    //
    public function updateCustomerRating(int $rating)
    {
        $this->assertActive();
        $this->customer->updateRating($rating);
    }
    
    public function updateJourney(CustomerJourney $customerJourney): void
    {
        $this->assertActive();
        $customerJourney->assertActive();
        //
        $this->customerJourney = $customerJourney;
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

    public function assertActive(): void
    {
        if ($this->status !== CustomerAssignmentStatus::ACTIVE) {
            throw RegularException::forbidden('inactive assignment');
        }
    }

    public function assertNoPendingClosingRequest(): void
    {
        $criteria = Criteria::create()
                ->andWhere(Criteria::create()->expr()->eq('status', ManagementApprovalStatus::WAITING_FOR_APPROVAL));
        if (!$this->closingRequests->matching($criteria)->isEmpty()) {
            throw RegularException::forbidden('this assignment has pending closing request');
        }
    }

    //
    public function submitNonScheduledSalesActivityReport(
            SalesActivity $salesActivity, string $reportId, SalesActivityReportData $salesActivityReportData): SalesActivityReport
    {
        $this->assertActive();
        return $this->customerAssignment->submitNonScheduledSalesActivityReport($salesActivity, $reportId,
                        $salesActivityReportData);
    }

    public function submitSalesActivitySchedule(
            SalesActivity $salesActivity, string $scheduleId, SalesActivityScheduleData $salesActivityScheduleData): SalesActivitySchedule
    {
        $this->assertActive();
        return new SalesActivitySchedule(
                $this->customerAssignment, $salesActivity, $scheduleId, $salesActivityScheduleData);
    }
}
