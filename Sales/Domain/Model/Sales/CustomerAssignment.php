<?php

namespace Sales\Domain\Model\Sales;

use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObjectList;
use Resources\Uuid;
use Sales\Domain\DependencyModel\SalesActivity;
use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule\SalesActivityReport;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule\SalesActivityReportData;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivityScheduleData;
use Shared\Domain\ValueObject\HourlyTimeIntervalData;

#[Entity]
class CustomerAssignment
{

    #[OneToOne(targetEntity: GreetingAssignment::class, mappedBy: "customerAssignment")]
    protected ?GreetingAssignment $greetingAssignment;

    #[OneToOne(targetEntity: FactFindingAssignment::class, mappedBy: "customerAssignment")]
    protected ?FactFindingAssignment $factFindingAssignment;

    #[OneToOne(targetEntity: StrikingAssignment::class, mappedBy: "customerAssignment")]
    protected ?StrikingAssignment $strikingAssignment;

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected DateTimeImmutable $createdTime;

    #[FetchableObjectList(targetEntity: SalesActivitySchedule::class, joinColumnName: "CustomerAssignment_id",
                paginationRequired: false)]
    #[OneToMany(targetEntity: SalesActivitySchedule::class, mappedBy: "customerAssignment", cascade: ["persist"],
                fetch: 'EXTRA_LAZY')]
    protected Collection $salesActivitySchedules;

    protected function __construct()
    {
        
    }

    //
    public function isBelongsToSales(Sales $sales): bool
    {
        return $this->strikingAssignment?->isBelongsToSales($sales) || $this->factFindingAssignment?->isBelongsToSales($sales) || $this->greetingAssignment->isBelongsToSales($sales);
    }

    //
    public function submitNonScheduledSalesActivityReport(
            SalesActivity $salesActivity, string $reportId, SalesActivityReportData $salesActivityReportData): SalesActivityReport
    {
        $scheduleData = new SalesActivityScheduleData(new HourlyTimeIntervalData('now'));
        $salesActivitySchedule = new SalesActivitySchedule($this, $salesActivity, Uuid::generateUuid4(), $scheduleData);
        return new SalesActivityReport($salesActivitySchedule, $reportId, $salesActivityReportData);
    }
}
