<?php

namespace Sales\Domain\Model\Sales\CustomerAssignment;

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
use Sales\Domain\DependencyModel\SalesActivity;
use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\CustomerAssignment;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule\SalesActivityReport;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesActivityScheduleRepository;
use Shared\Domain\Enum\SalesActivityScheduleStatus;
use Shared\Domain\ValueObject\HourlyTimeInterval;

#[Entity(repositoryClass: DoctrineSalesActivityScheduleRepository::class)]
class SalesActivitySchedule
{

    #[ManyToOne(targetEntity: CustomerAssignment::class, inversedBy: "salesActivitySchedules", fetch: "LAZY")]
    #[JoinColumn(name: "CustomerAssignment_id", referencedColumnName: "id")]
    protected CustomerAssignment $customerAssignment;

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
    #[FetchableObject(targetEntity: SalesActivityReport::class, joinColumnName: "id",
                referenceColumnName: "SalesActivitySchedule_id")]
    protected SalesActivityReport $salesActivityReport;

    public function __construct(
            CustomerAssignment $customerAssignment, SalesActivity $salesActivity, string $id, SalesActivityScheduleData $data)
    {
        $salesActivity->assertActive();

        $this->customerAssignment = $customerAssignment;
        $this->salesActivity = $salesActivity;
        $this->id = $id;
        $this->createdTime = new \DateTimeImmutable();
        $this->schedule = new HourlyTimeInterval($data->hourlyTimeIntervalData);
        $this->status = SalesActivityScheduleStatus::SCHEDULED;
    }

    public function markAsCompleted(): void
    {
        $this->status = SalesActivityScheduleStatus::COMPLETED;
    }

    //
    public function assertIncomplete(): void
    {
        if ($this->status !== SalesActivityScheduleStatus::SCHEDULED) {
            throw RegularException::forbidden('schedule already completed');
        }
    }
    
    public function assertBelongsToSales(Sales $sales): void
    {
        if (!$this->customerAssignment->isBelongsToSales($sales)) {
            throw RegularException::forbidden('unmanaged schedule');
        }
    }
}
