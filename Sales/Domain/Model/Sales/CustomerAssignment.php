<?php

namespace Sales\Domain\Model\Sales;

use Company\Domain\Model\Customer as FetchableCustomerFromCompanyBC;
use Company\Domain\Model\CustomerJourney as CustomerJourneyInCompanyBC;
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
use Resources\Infrastructure\GraphQL\Attributes\IncludeAsInput;
use Resources\Uuid;
use Sales\Domain\DependencyModel\Customer;
use Sales\Domain\DependencyModel\Customer\VerificationReportData;
use Sales\Domain\DependencyModel\CustomerData;
use Sales\Domain\DependencyModel\CustomerJourney;
use Sales\Domain\DependencyModel\CustomerVerification;
use Sales\Domain\DependencyModel\Province\City;
use Sales\Domain\DependencyModel\SalesActivity;
use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\CustomerAssignment\ClosingRequest;
use Sales\Domain\Model\Sales\CustomerAssignment\ClosingRequestData;
use Sales\Domain\Model\Sales\CustomerAssignment\RecycleRequest;
use Sales\Domain\Model\Sales\CustomerAssignment\RecycleRequestData;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivityScheduleData;
use Sales\Domain\Service\SalesActivitySchedulerService;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineCustomerAssignmentRepository;
use SharedContext\Domain\Enum\CustomerAssignmentStatus;
use SharedContext\Domain\Enum\SalesActivityScheduleStatus;
use SharedContext\Domain\Event\CustomerAssignedEvent;
use SharedContext\Domain\ValueObject\HourlyTimeIntervalData;

#[Entity(repositoryClass: DoctrineCustomerAssignmentRepository::class)]
class CustomerAssignment implements ContainEventsInterface
{

    use ContainEventsTrait;

    #[ManyToOne(targetEntity: Sales::class, inversedBy: "customerAssignments", fetch: "LAZY")]
    #[JoinColumn(name: "Sales_id", referencedColumnName: "id")]
    protected Sales $sales;

    #[FetchableObject(targetEntity: FetchableCustomerFromCompanyBC::class, joinColumnName: "Customer_id")]
    #[IncludeAsInput(targetEntity: Customer::class)]
    #[ManyToOne(targetEntity: Customer::class, cascade: ["persist"])]
    #[JoinColumn(name: "Customer_id", referencedColumnName: "id")]
    protected Customer $customer;

    #[FetchableObject(targetEntity: CustomerJourneyInCompanyBC::class, joinColumnName: "CustomerJourney_id")]
    #[ManyToOne(targetEntity: CustomerJourney::class)]
    #[JoinColumn(name: "CustomerJourney_id", referencedColumnName: "id")]
    protected ?CustomerJourney $customerJourney;

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "string", enumType: CustomerAssignmentStatus::class)]
    protected CustomerAssignmentStatus $status;

    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected DateTimeImmutable $createdTime;

    #[FetchableObjectList(targetEntity: ClosingRequest::class, joinColumnName: "CustomerAssignment_id",
                paginationRequired: false)]
    #[OneToMany(targetEntity: ClosingRequest::class, mappedBy: "customerAssignment")]
    protected Collection $closingRequests;

    #[FetchableObjectList(targetEntity: RecycleRequest::class, joinColumnName: "CustomerAssignment_id",
                paginationRequired: false)]
    #[OneToMany(targetEntity: RecycleRequest::class, mappedBy: "customerAssignment")]
    protected Collection $recycleRequests;

    #[FetchableObjectList(targetEntity: SalesActivitySchedule::class, joinColumnName: "CustomerAssignment_id",
                paginationRequired: false)]
    #[OneToMany(targetEntity: SalesActivitySchedule::class, mappedBy: "customerAssignment", cascade: ["persist"],
                fetch: 'EXTRA_LAZY')]
    protected Collection $salesActivitySchedules;

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
        $this->status = CustomerAssignmentStatus::ACTIVE;
        $this->createdTime = new DateTimeImmutable();
        //
        $this->sales->assertActive();
        $this->customerJourney?->assertActive();
        //
        $this->salesActivitySchedules = new ArrayCollection();

        $this->recordEvent(new CustomerAssignedEvent($this->id));
    }

    public function updateJourney(CustomerJourney $customerJourney): void
    {
        $customerJourney->assertActive();
        $this->customerJourney = $customerJourney;
    }

    public function updateCustomer(City $city, CustomerData $customerData): void
    {
        $this->assertActive();
        $this->customer->update($city, $customerData);
    }

    public function SubmitCustomerVerificationReport(
            CustomerVerification $customerVerification, VerificationReportData $verificationReportData): void
    {
        $this->assertActive();
        $this->customer->submitVerificationReport($customerVerification, $verificationReportData);
    }

    //
    public function assertBelongsToSales(Sales $sales): void
    {
        if ($this->sales !== $sales) {
            throw RegularException::forbidden('unmanaged assigned customer');
        }
    }

    public function assertActive(): void
    {
        if ($this->status !== CustomerAssignmentStatus::ACTIVE) {
            throw RegularException::forbidden('inactive customer assignment');
        }
    }

    //
    public function submitSalesActivitySchedule(
            SalesActivity $salesActivity, SalesActivityScheduleData $scheduledSalesActivityData): SalesActivitySchedule
    {
        $this->assertActive();
        return new SalesActivitySchedule($this, $salesActivity, $scheduledSalesActivityData);
    }

    //
    protected function assertNoOngoingRequest(): void
    {
        $closingRequestFilter = fn(ClosingRequest $closingRequest) => $closingRequest->isOngoing();
        $containOngoingClosingRequest = !$this->closingRequests->filter($closingRequestFilter)->isEmpty();

        $recycleRequestFilter = fn(RecycleRequest $recycleRequest) => $recycleRequest->isOngoing();
        $containOngoingRecycleRequest = !$this->recycleRequests->filter($recycleRequestFilter)->isEmpty();

        if ($containOngoingClosingRequest || $containOngoingRecycleRequest) {
            throw RegularException::forbidden('there area still ongoing closing/recycle request on this assignment');
        }
    }

    public function submitClosingRequest(ClosingRequestData $closingRequestData): ClosingRequest
    {
        $this->assertActive();
        $this->assertNoOngoingRequest();
        return new ClosingRequest($this, $closingRequestData);
    }

    public function submitRecycleRequest(RecycleRequestData $recycleRequestData): RecycleRequest
    {
        $this->assertActive();
        $this->assertNoOngoingRequest();
        return new RecycleRequest($this, $recycleRequestData);
    }

    //
    public function addUpcomingScheduleToSchedulerService(SalesActivitySchedulerService $service): void
    {
        $criteria = Criteria::create()
                ->andWhere(Criteria::expr()->gte('schedule.startTime', new DateTimeImmutable()))
                ->andWhere(Criteria::expr()->eq('status', SalesActivityScheduleStatus::SCHEDULED));
        foreach ($this->salesActivitySchedules->matching($criteria)->getIterator() as $schedule) {
            $schedule->includeInSchedulerService($service);
        }
    }

    public function initiateSalesActivitySchedule(SalesActivity $initialSalesActivity,
            SalesActivitySchedulerService $schedulerService): void
    {
        $this->sales->registerAllUpcomingScheduleToScheduler($schedulerService);

        $startTime = $schedulerService->nextAvailableTimeSlotForScheduleWithDuration($initialSalesActivity->getDuration())->format('Y-m-d H:i:s');
        $hourlyTimeIntervalData = new HourlyTimeIntervalData($startTime);
        $scheduledSalesActivityData = (new SalesActivityScheduleData($hourlyTimeIntervalData))->setId(Uuid::generateUuid4());

        $salesActivitySchedule = $this->submitSalesActivitySchedule($initialSalesActivity, $scheduledSalesActivityData);
        $this->salesActivitySchedules->add($salesActivitySchedule);
    }
}
