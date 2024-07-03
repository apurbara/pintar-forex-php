<?php

namespace Company\Domain\Model;

use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineCommonSalesMetricRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Resources\ValidationRule;
use Resources\ValidationService;
use Shared\Domain\Enum\EvaluationType;
use Shared\Domain\Enum\MetricType;
use Shared\Domain\Enum\RecurrenceType;

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

    #[Column(type: "string", enumType: MetricType::class)]
    protected MetricType $metricType;

    #[Column(type: "string", enumType: EvaluationType::class)]
    protected EvaluationType $evaluationType;

    #[Column(type: "string", enumType: RecurrenceType::class)]
    protected RecurrenceType $recurrenceType;

    #[Column(type: "smallint", nullable: true)]
    protected ?int $recurrenceCount;

    #[Column(type: "string", length: 1024, nullable: true)]
    protected ?string $displaySchema;

    private function setName(?string $name)
    {
        ValidationService::build()
                ->addRule(ValidationRule::notEmpty())
                ->execute($name, 'name is mandatory');
        $this->name = $name;
    }

    public function __construct(string $id, CommonSalesMetricData $data)
    {
        $this->id = $id;
        $this->disabled = false;
        $this->createdTime = new DateTimeImmutable();
        $this->update($data);
    }

    public function update(CommonSalesMetricData $data): void
    {
        $this->lastModifiedTime = new DateTimeImmutable();
        $this->setName($data->name);
        $this->target = $data->target;
        $this->metricType = MetricType::from($data->metricType);
        $this->evaluationType = EvaluationType::from($data->evaluationType);
        $this->recurrenceType = RecurrenceType::from($data->recurrenceType);
        $this->recurrenceCount = $data->recurrenceCount;
        $this->displaySchema = $data->displaySchema;
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
