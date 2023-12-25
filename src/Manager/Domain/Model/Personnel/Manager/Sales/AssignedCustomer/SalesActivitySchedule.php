<?php

namespace Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer;

use Company\Domain\Model\SalesActivity as SalesActivityInCompanyBC;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer\SalesActivitySchedule\SalesActivityReport;
use Manager\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesActivityScheduleRepository;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;
use SharedContext\Domain\Enum\SalesActivityScheduleStatus;
use SharedContext\Domain\ValueObject\HourlyTimeInterval;

#[Entity(repositoryClass: DoctrineSalesActivityScheduleRepository::class)]
class SalesActivitySchedule
{

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected DateTimeImmutable $createdTime;

    #[Embedded(class: HourlyTimeInterval::class, columnPrefix: false)]
    protected HourlyTimeInterval $schedule;

    #[Column(type: "string", enumType: SalesActivityScheduleStatus::class)]
    protected SalesActivityScheduleStatus $status;

    //
    #[FetchableObject(targetEntity: AssignedCustomer::class, joinColumnName: "AssignedCustomer_id")]
    #[JoinColumn(name: "AssignedCustomer_id", referencedColumnName: "id")]
    protected $assignedCustomer;

    #[FetchableObject(targetEntity: SalesActivityInCompanyBC::class, joinColumnName: "SalesActivity_id")]
    #[JoinColumn(name: "SalesActivity_id", referencedColumnName: "id")]
    protected $salesActivity;

    #[FetchableObject(targetEntity: SalesActivityReport::class, joinColumnName: "id", referenceColumnName: "SalesActivitySchedule_id")]
    protected $salesActivityReport;

    protected function __construct()
    {
        
    }
}
