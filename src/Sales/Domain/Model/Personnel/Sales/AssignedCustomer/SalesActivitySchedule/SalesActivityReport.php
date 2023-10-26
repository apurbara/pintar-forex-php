<?php

namespace Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivitySchedule;

use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Resources\Attributes\FetchableEntity;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivitySchedule;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesActivityReportRepository;

#[Entity(repositoryClass: DoctrineSalesActivityReportRepository::class)]
class SalesActivityReport
{

    #[FetchableEntity(targetEntity: SalesActivitySchedule::class, joinColumnName: "SalesActivitySchedule_id")]
    #[ManyToOne(targetEntity: SalesActivitySchedule::class)]
    #[JoinColumn(name: "SalesActivitySchedule_id", referencedColumnName: "id")]
    protected SalesActivitySchedule $salesActivitySchedule;
    
    #[Id, Column(type: "guid")]
    protected string $id;
    
    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected DateTimeImmutable $submitTime;
    
    #[Column(type: "text", nullable: true)]
    protected string $content;

    public function __construct(SalesActivitySchedule $salesActivitySchedule, SalesActivityReportData $data)
    {
        $this->salesActivitySchedule = $salesActivitySchedule;
        $this->id = $data->id;
        $this->submitTime = new \DateTimeImmutable();
        $this->content = $data->content;
    }
}
