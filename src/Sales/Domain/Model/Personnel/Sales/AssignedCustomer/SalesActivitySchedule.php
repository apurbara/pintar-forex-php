<?php

namespace Sales\Domain\Model\Personnel\Sales\AssignedCustomer;

use Company\Domain\Model\SalesActivity as SalesActivityInCompanyBC;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Resources\Exception\RegularException;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;
use Sales\Domain\Model\Personnel\Sales;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivitySchedule\SalesActivityReport;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivitySchedule\SalesActivityReportData;
use Sales\Domain\Model\SalesActivity;
use Sales\Domain\Service\SalesActivitySchedulerService;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesActivityScheduleRepository;
use SharedContext\Domain\Enum\SalesActivityScheduleStatus;
use SharedContext\Domain\ValueObject\HourlyTimeInterval;
use SharedContext\Domain\ValueObject\HourlyTimeIntervalData;

#[Entity(repositoryClass: DoctrineSalesActivityScheduleRepository::class)]
class SalesActivitySchedule
{

    #[FetchableObject(targetEntity: AssignedCustomer::class, joinColumnName: "AssignedCustomer_id")]
    #[ManyToOne(targetEntity: AssignedCustomer::class, inversedBy: "salesActivitySchedules", fetch: "LAZY")]
    #[JoinColumn(name: "AssignedCustomer_id", referencedColumnName: "id")]
    protected AssignedCustomer $assignedCustomer;

    #[FetchableObject(targetEntity: SalesActivityInCompanyBC::class, joinColumnName: "SalesActivity_id")]
    #[ManyToOne(targetEntity: SalesActivity::class)]
    #[JoinColumn(name: "SalesActivity_id", referencedColumnName: "id")]
    protected SalesActivity $salesActivity;

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected DateTimeImmutable $createdTime;

    #[Embedded(class: HourlyTimeInterval::class, columnPrefix: false)]
    protected HourlyTimeInterval $schedule;

    #[Column(type: "string", enumType: SalesActivityScheduleStatus::class)]
    protected SalesActivityScheduleStatus $status;
    
    //
    #[FetchableObject(targetEntity: SalesActivityReport::class, joinColumnName: "id", referenceColumnName: "SalesActivitySchedule_id")]
    protected SalesActivityReport $salesActivityReport;

    public function getStatus(): SalesActivityScheduleStatus
    {
        return $this->status;
    }

    public function getSchedule(): HourlyTimeInterval
    {
        return $this->schedule;
    }

    public function getActivityDuration(): int
    {
        return $this->salesActivity->getDuration();
    }

    public function isRelocateable(): bool
    {
        return $this->salesActivity->isInitial();
    }

    public function __construct(
            AssignedCustomer $assignedCustomer, SalesActivity $salesActivity, SalesActivityScheduleData $data)
    {
        $salesActivity->assertActive();

        $this->assignedCustomer = $assignedCustomer;
        $this->salesActivity = $salesActivity;
        $this->id = $data->id;
        $this->createdTime = new \DateTimeImmutable();
        $this->schedule = new HourlyTimeInterval($data->hourlyTimeIntervalData);
        $this->status = SalesActivityScheduleStatus::SCHEDULED;
    }

    public function relocateTo(\DateTimeImmutable $startTime): void
    {
        $this->schedule = new HourlyTimeInterval(new HourlyTimeIntervalData($startTime->format('Y-m-d H:i:s')));
    }

    //
    public function assertBelongsToSales(Sales $sales): void
    {
        $this->assignedCustomer->assertBelongsToSales($sales);
    }

    //
    public function submitReport(SalesActivityReportData $salesActivityReportData): SalesActivityReport
    {
        if ($this->status !== SalesActivityScheduleStatus::SCHEDULED) {
            throw RegularException::forbidden('schedule concluded');
        }
        $this->status = SalesActivityScheduleStatus::COMPLETED;
        return new SalesActivityReport($this, $salesActivityReportData);
    }

    //
    public function includeInSchedulerService(SalesActivitySchedulerService $service): void
    {
        if ($this->schedule->getStartTime() > new DateTimeImmutable()) {
            $service->add($this->schedule->getStartTime(), $this);
        }
    }

    public function relocateConflictedInitialScheduleIfDurationNotEnough(SalesActivitySchedulerService $schedulerService): void
    {
        $schedulerService->releaseRequiredDurationInTimeSlotOrDie($this->schedule->getStartTime(),
                $this->getActivityDuration());
    }
}
