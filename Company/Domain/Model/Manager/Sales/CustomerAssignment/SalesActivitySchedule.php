<?php

namespace Company\Domain\Model\Manager\Sales\CustomerAssignment;

use Company\Domain\Model\Manager\Sales\Assignment\SalesActivitySchedule\SalesActivityReport;
use Company\Domain\Model\SalesActivity;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesActivityScheduleRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;
use Sales\Domain\Model\Sales\CustomerAssignment;
use Shared\Domain\Enum\SalesActivityScheduleStatus;
use Shared\Domain\ValueObject\HourlyTimeInterval;

#[Entity(repositoryClass: DoctrineSalesActivityScheduleRepository::class)]
class SalesActivitySchedule
{

    #[ManyToOne(targetEntity: CustomerAssignment::class, inversedBy: "salesActivitySchedules", fetch: "LAZY")]
    #[JoinColumn(name: "CustomerAssignment_id", referencedColumnName: "id")]
    protected CustomerAssignment $customerAssignment;

    #[FetchableObject(targetEntity: SalesActivity::class, joinColumnName: "SalesActivity_id")]
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

    public function getStatus(): SalesActivityScheduleStatus
    {
        return $this->status;
    }

    protected function __construct()
    {
        
    }

    public function cancelBySystem()
    {
        if ($this->status == SalesActivityScheduleStatus::SCHEDULED) {
            $this->status = SalesActivityScheduleStatus::CANCELLED_BY_SYSTEM;
        }
    }
}
