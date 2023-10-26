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
use Resources\Attributes\FetchableEntity;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;
use Sales\Domain\Model\SalesActivity;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineScheduledSalesActivityRepository;
use SharedContext\Domain\Enum\ScheduledSalesActivityStatus;
use SharedContext\Domain\ValueObject\HourlyTimeInterval;

#[Entity(repositoryClass: DoctrineScheduledSalesActivityRepository::class)]
class ScheduledSalesActivity
{
    
    #[FetchableEntity(targetEntity: AssignedCustomer::class, joinColumnName: "AssignedCustomer_id")]
    #[ManyToOne(targetEntity: AssignedCustomer::class)]
    #[JoinColumn(name: "AssignedCustomer_id", referencedColumnName: "id")]
    protected AssignedCustomer $assignedCustomer;
    
    #[FetchableEntity(targetEntity: SalesActivityInCompanyBC::class, joinColumnName: "SalesActivity_id")]
    #[ManyToOne(targetEntity: SalesActivity::class)]
    #[JoinColumn(name: "SalesActivity_id", referencedColumnName: "id")]
    protected SalesActivity $salesActivity;
    
    #[Id, Column(type: "guid")]
    protected string $id;
    
    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected DateTimeImmutable $createdTime;
    
    #[Embedded(class: HourlyTimeInterval::class, columnPrefix: false)]
    protected HourlyTimeInterval $schedule;
    
    #[Column(type: "string", enumType: ScheduledSalesActivityStatus::class)]
    protected ScheduledSalesActivityStatus $status;

    public function __construct(
            AssignedCustomer $assignedCustomer, SalesActivity $salesActivity, ScheduledSalesActivityData $data)
    {
        $salesActivity->assertActive();
        
        $this->assignedCustomer = $assignedCustomer;
        $this->salesActivity = $salesActivity;
        $this->id = $data->id;
        $this->createdTime = new \DateTimeImmutable();
        $this->schedule = new HourlyTimeInterval($data->hourlyTimeIntervalData);
        $this->status = ScheduledSalesActivityStatus::SCHEDULED;
    }

    //
    public function submitReport()
    {
        
    }
}
