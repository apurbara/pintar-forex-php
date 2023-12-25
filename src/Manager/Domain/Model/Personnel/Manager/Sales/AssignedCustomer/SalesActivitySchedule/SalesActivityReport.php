<?php

namespace Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer\SalesActivitySchedule;

use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer\SalesActivitySchedule;
use Manager\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesActivityReportRepository;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;

#[Entity(repositoryClass: DoctrineSalesActivityReportRepository::class)]
class SalesActivityReport
{

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected DateTimeImmutable $submitTime;

    #[Column(type: "text", nullable: true)]
    protected string $content;

    //
    #[FetchableObject(targetEntity: SalesActivitySchedule::class, joinColumnName: "SalesActivitySchedule_id")]
    #[JoinColumn(name: "SalesActivitySchedule_id", referencedColumnName: "id")]
    protected $salesActivitySchedule;

    protected function __construct()
    {
        
    }
}
