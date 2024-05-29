<?php

namespace Company\Domain\Model;

use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineCommonSalesMetricRepository;
use SharedContext\Domain\Enum\EvaluationType;
use SharedContext\Domain\Enum\RecurrenceType;
use SharedContext\Domain\Enum\SalesMetricType;

#[Entity(repositoryClass: DoctrineCommonSalesMetricRepository::class)]
class CommonSalesMetric
{

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $disabled;

    #[Column(type: "datetimetz_immutable", nullable: false, options: ["default" => "CURRENT_TIMESTAMP"])]
    protected DateTimeImmutable $createdTime;

    #[Column(type: "datetimetz_immutable", nullable: false, options: ["default" => "CURRENT_TIMESTAMP"])]
    protected DateTimeImmutable $lastModifiedTime;

    #[Column(type: "string", length: 255, nullable: false)]
    protected string $name;

    #[Column(type: "integer", nullable: true)]
    protected int $target;

    #[Column(type: "string", enumType: SalesMetricType::class)]
    protected SalesMetricType $metricType;

    #[Column(type: "string", enumType: EvaluationType::class)]
    protected EvaluationType $evaluationType;

    #[Column(type: "string", enumType: RecurrenceType::class)]
    protected RecurrenceType $recurrenceType;

    #[Column(type: "smallint", nullable: true)]
    protected ?int $recurrenceCount;

    #[Column(type: "string", length: 1024, nullable: true)]
    protected ?string $displaySchema;

    public function __construct(string $id, CommonSalesMetricData $data)
    {
//        $this->id = $id;
//        $this->disabled = $disabled;
//        $this->createdTime = $createdTime;
//        $this->lastModifiedTime = $lastModifiedTime;
//        $this->name = $name;
//        $this->target = $target;
//        $this->metricType = $metricType;
//        $this->evaluationType = $evaluationType;
//        $this->recurrenceType = $recurrenceType;
//        $this->recurrenceCount = $recurrenceCount;
//        $this->displaySchema = $displaySchema;
    }

    public function update(CommonSalesMetricData $data): void
    {
        
    }

    public function disable(): void
    {
        
    }

    public function enable(): void
    {
        
    }
}
