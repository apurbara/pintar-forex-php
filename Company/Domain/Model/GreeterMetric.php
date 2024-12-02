<?php

namespace Company\Domain\Model;

use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineGreeterMetricRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Shared\Domain\Enum\EvaluationType;
use Shared\Domain\Enum\RecurrenceType;
use Shared\Domain\Enum\SalesMetricType;

#[Entity(repositoryClass: DoctrineGreeterMetricRepository::class)]
class GreeterMetric
{

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $disabled;

    #[Column(type: "datetimetz_immutable", nullable: false, options: ["default" => "CURRENT_TIMESTAMP"])]
    protected DateTimeImmutable $createdTime;

    #[Column(type: "integer", nullable: true)]
    protected int $monthlyTarget;

    #[Column(type: "integer", nullable: true)]
    protected ?int $dailyReminderTarget;

    #[Column(type: "string", enumType: SalesMetricType::class)]
    protected SalesMetricType $salesMetricType;

    #[Column(type: "string", enumType: EvaluationType::class)]
    protected EvaluationType $evaluationType;

    #[Column(type: "string", enumType: RecurrenceType::class)]
    protected RecurrenceType $recurrenceType;

    #[Column(type: "smallint", nullable: true)]
    protected ?int $recurrenceCount;

    public function __construct(string $id, GreeterMetricData $data)
    {
        $this->id = $id;
        $this->disabled = false;
        $this->createdTime = new DateTimeImmutable();
        $this->update($data);
    }

    public function update(GreeterMetricData $data): void
    {
        $this->monthlyTarget = $data->monthlyTarget;
        $this->dailyReminderTarget = $data->dailyReminderTarget;
        $this->salesMetricType = SalesMetricType::from($data->salesMetricType);
        $this->evaluationType = EvaluationType::from($data->evaluationType);
        $this->recurrenceType = RecurrenceType::from($data->recurrenceType);
        $this->recurrenceCount = $data->recurrenceCount;
    }

    public function disable(): void
    {
        $this->disabled = true;
    }

    public function enable(): void
    {
        $this->disabled = false;
    }
}
