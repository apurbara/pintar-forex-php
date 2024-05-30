<?php

namespace Company\Domain\Model;

use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineCompanyMetricRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Resources\ValidationRule;
use Resources\ValidationService;
use SharedContext\Domain\Enum\EvaluationType;
use SharedContext\Domain\Enum\ManagementApprovalStatus;
use SharedContext\Domain\Enum\MetricType;
use SharedContext\Domain\Enum\RecurrenceType;

#[Entity(repositoryClass: DoctrineCompanyMetricRepository::class)]
class CompanyMetric
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

    protected function setName(string $name)
    {
        ValidationService::build()
                ->addRule(ValidationRule::notEmpty())
                ->execute($name, 'name is mandatory');
        $this->name = $name;
    }

    public function __construct(string $id, CompanyMetricData $data)
    {
        $this->id = $id;
        $this->disabled = false;
        $this->createdTime = new \DateTimeImmutable();
        $this->update($data);
    }

    public function update(CompanyMetricData $data): void
    {
        $this->lastModifiedTime = new \DateTimeImmutable();
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

    //
    public function fetchSummaryResult(Connection $connection): array
    {
        $qb = $connection->createQueryBuilder();
        match ($this->metricType) {
            MetricType::SALES_ACTIVITY_REPORT => $this->applySalesActivityReportMetric($qb),
            MetricType::APPROVED_CLOSING_REQUEST => $this->applyApprovedClosingRequestMetric($qb)
        };

        return [
            'name' => $this->name,
            'target' => $this->target,
            'result' => $qb->executeQuery()->fetchAllAssociative(),
        ];
    }

    protected function applySalesActivityReportMetric(QueryBuilder $qb): void
    {
        $qb->from('SalesActivityReport');
        $this->recurrenceType->applyToQuery($qb, 'SalesActivityReport.submitTime', $this->recurrenceCount);
        $this->evaluationType->applyToQuery($qb, 'SalesActivityReport.id');
    }

    protected function applyApprovedClosingRequestMetric(QueryBuilder $qb): void
    {
        $approvedClosingRequestStatus = ManagementApprovalStatus::APPROVED->value;
        $qb->from('ClosingRequest')
                ->andWhere($qb->expr()->eq('ClosingRequest.status', "'{$approvedClosingRequestStatus}'"));
        $this->recurrenceType->applyToQuery($qb, 'ClosingRequest.createdTime', $this->recurrenceCount);
        $this->evaluationType->applyToQuery($qb, 'ClosingRequest.transactionValue');
    }
}
