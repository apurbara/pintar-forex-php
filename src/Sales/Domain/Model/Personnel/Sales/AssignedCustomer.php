<?php

namespace Sales\Domain\Model\Personnel\Sales;

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
use Resources\Uuid;
use Sales\Domain\Model\AreaStructure\Area\Customer;
use Sales\Domain\Model\AreaStructure\Area\Customer\VerificationReportData;
use Sales\Domain\Model\CustomerJourney;
use Sales\Domain\Model\CustomerVerification;
use Sales\Domain\Model\Personnel\Sales;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\ClosingRequest;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\ClosingRequestData;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\RecycleRequest;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\RecycleRequestData;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivitySchedule;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivityScheduleData;
use Sales\Domain\Model\SalesActivity;
use Sales\Domain\Service\SalesActivitySchedulerService;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineAssignedCustomerRepository;
use SharedContext\Domain\Enum\CustomerAssignmentStatus;
use SharedContext\Domain\Enum\SalesActivityScheduleStatus;
use SharedContext\Domain\Event\CustomerAssignedEvent;
use SharedContext\Domain\ValueObject\HourlyTimeIntervalData;

#[Entity(repositoryClass: DoctrineAssignedCustomerRepository::class)]
class AssignedCustomer implements ContainEventsInterface
{

    use ContainEventsTrait;

    #[FetchableObject(targetEntity: Sales::class, joinColumnName: "Sales_id")]
    #[ManyToOne(targetEntity: Sales::class, inversedBy: "assignedCustomers", fetch: "LAZY")]
    #[JoinColumn(name: "Sales_id", referencedColumnName: "id")]
    protected Sales $sales;

    #[FetchableObject(targetEntity: Customer::class, joinColumnName: "Customer_id")]
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

    #[FetchableObjectList(targetEntity: ClosingRequest::class, joinColumnName: "AssignedCustomer_id", paginationRequired: false)]
    #[OneToMany(targetEntity: ClosingRequest::class, mappedBy: "assignedCustomer")]
    protected Collection $closingRequests;
    
    #[FetchableObjectList(targetEntity: RecycleRequest::class, joinColumnName: "AssignedCustomer_id", paginationRequired: false)]
    #[OneToMany(targetEntity: RecycleRequest::class, mappedBy: "assignedCustomer")]
    protected Collection $recycleRequests;
    
    #[FetchableObjectList(targetEntity: SalesActivitySchedule::class, joinColumnName: "AssignedCustomer_id", paginationRequired: false)]
    #[OneToMany(targetEntity: SalesActivitySchedule::class, mappedBy: "assignedCustomer", cascade: ["persist"], fetch: 'EXTRA_LAZY')]
    protected Collection $salesActivitySchedules;
    
    public function getStatus(): CustomerAssignmentStatus
    {
        return $this->status;
    }

    public function __construct(Sales $sales, Customer $customer, ?CustomerJourney $customerJourney, string $id)
    {
        $customerJourney?->assertActive();
        
        $this->sales = $sales;
        $this->customer = $customer;
        $this->customerJourney = $customerJourney;
        $this->id = $id;
        $this->status = CustomerAssignmentStatus::ACTIVE;
        $this->createdTime = new DateTimeImmutable();
        
        $this->salesActivitySchedules = new ArrayCollection();
        

        $this->recordEvent(new CustomerAssignedEvent($this->id));
    }
    
    public function updateJourney(CustomerJourney $customerJourney): void
    {
        $customerJourney->assertActive();
        $this->customerJourney = $customerJourney;
    }

    //
    public function assertBelongsToSales(Sales $sales): void
    {
        if ($this->sales !== $sales) {
            throw RegularException::forbidden('unmanaged assigned customer');
        }
    }

    protected function assertActive(): void
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

    public function submitVerificationReport(
            CustomerVerification $customerVerification, VerificationReportData $verificationReportData): void
    {
        $this->assertActive();
        $this->customer->submitVerificationReport($customerVerification, $verificationReportData);
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
                ->andWhere(Criteria::expr()->gte('schedule.startTime',  new DateTimeImmutable()))
                ->andWhere(Criteria::expr()->eq('status', SalesActivityScheduleStatus::SCHEDULED));
        foreach ($this->salesActivitySchedules->matching($criteria)->getIterator() as $schedule) {
            $schedule->includeInSchedulerService($service);
        }
    }
    
    public function initiateSalesActivitySchedule(SalesActivity $initialSalesActivity, SalesActivitySchedulerService $schedulerService): void
    {
        $this->sales->registerAllUpcomingScheduleToScheduler($schedulerService);
        
        $startTime = $schedulerService->nextAvailableTimeSlotForScheduleWithDuration($initialSalesActivity->getDuration())->format('Y-m-d H:i:s');
        $hourlyTimeIntervalData = new HourlyTimeIntervalData($startTime);
        $scheduledSalesActivityData = (new SalesActivityScheduleData($hourlyTimeIntervalData))->setId(Uuid::generateUuid4());
        
        $salesActivitySchedule = $this->submitSalesActivitySchedule($initialSalesActivity, $scheduledSalesActivityData);
        $this->salesActivitySchedules->add($salesActivitySchedule);
    }
    
}
