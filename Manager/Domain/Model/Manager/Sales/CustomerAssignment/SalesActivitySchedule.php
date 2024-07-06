<?php

namespace Manager\Domain\Model\Manager\Sales\CustomerAssignment;

use Company\Domain\Model\SalesActivity;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Manager\Domain\Model\Manager\Sales\CustomerAssignment;
use Manager\Domain\Model\Manager\Sales\CustomerAssignment\SalesActivitySchedule\SalesActivityReport;
use Manager\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesActivityScheduleRepository;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;
use Shared\Domain\Enum\SalesActivityScheduleStatus;
use Shared\Domain\ValueObject\HourlyTimeInterval;

#[Entity(repositoryClass: DoctrineSalesActivityScheduleRepository::class)]
class SalesActivitySchedule
{

    #[FetchableObject(targetEntity: CustomerAssignment::class, joinColumnName: "CustomerAssignment_id")]
    #[ManyToOne(targetEntity: CustomerAssignment::class, inversedBy: "salesActivitySchedules", fetch: "LAZY")]
    #[JoinColumn(name: "CustomerAssignment_id", referencedColumnName: "id")]
    protected CustomerAssignment $customerAssignment;

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

    #[FetchableObject(targetEntity: SalesActivity::class, joinColumnName: "SalesActivity_id")]
    #[JoinColumn(name: "SalesActivity_id", referencedColumnName: "id")]
    protected SalesActivity $salesActivity;
    
    protected  function __construct()
    {
    }
}
