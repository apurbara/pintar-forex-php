<?php

namespace Company\Domain\Model\Manager\Sales\CustomerAssignment\SalesActivitySchedule;

use Company\Domain\Model\Manager\Sales\CustomerAssignment\SalesActivitySchedule;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesActivityReportRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;

#[Entity(repositoryClass: DoctrineSalesActivityReportRepository::class)]
class SalesActivityReport
{

    #[FetchableObject(targetEntity: SalesActivitySchedule::class, joinColumnName: "SalesActivitySchedule_id")]
    #[ManyToOne(targetEntity: SalesActivitySchedule::class)]
    #[JoinColumn(name: "SalesActivitySchedule_id", referencedColumnName: "id")]
    protected SalesActivitySchedule $salesActivitySchedule;

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected DateTimeImmutable $submitTime;

    #[Column(type: "text", nullable: true)]
    protected string $content;

    protected function __construct()
    {
        
    }
}
