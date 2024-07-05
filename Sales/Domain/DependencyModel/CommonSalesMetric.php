<?php

namespace Sales\Domain\DependencyModel;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineCommonSalesMetricRepository;
use SharedContext\Domain\Enum\EvaluationType;
use SharedContext\Domain\Enum\ManagementApprovalStatus;
use SharedContext\Domain\Enum\MetricType;
use SharedContext\Domain\Enum\RecurrenceType;

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

    protected function __construct()
    {
        
    }
    
    public function fetchSummaryResult(Connection $connection, string $salesId): array
    {
        $qb = $connection->createQueryBuilder();
        match ($this->metricType) {
            MetricType::SALES_ACTIVITY_REPORT => $this->applySalesActivityReportMetric($qb, $salesId),
            MetricType::APPROVED_CLOSING_REQUEST => $this->applyApprovedClosingRequestMetric($qb, $salesId)
        };

        return [
            'name' => $this->name,
            'target' => $this->target,
            'result' => $qb->executeQuery()->fetchAllAssociative(),
        ];
    }
    
    protected function applySalesActivityReportMetric(QueryBuilder $qb, string $salesId): void
    {
        $qb->from('SalesActivityReport')
                ->innerJoin('SalesActivityReport', 'SalesActivitySchedule', 'SalesActivitySchedule',
                        'SalesActivityReport.SalesActivitySchedule_id = SalesActivitySchedule.id')
                ->innerJoin('SalesActivitySchedule', 'CustomerAssignment', 'CustomerAssignment',
                        'SalesActivitySchedule.CustomerAssignment_id = CustomerAssignment.id')
                ->andWhere($qb->expr()->eq('CustomerAssignment.Sales_id', ':salesId'))
                ->setParameter('salesId', $salesId);
        $this->recurrenceType->applyToQuery($qb, 'SalesActivityReport.submitTime', $this->recurrenceCount);
        $this->evaluationType->applyToQuery($qb, 'SalesActivityReport.id');
    }

    protected function applyApprovedClosingRequestMetric(QueryBuilder $qb, string $salesId): void
    {
        $approvedClosingRequestStatus = ManagementApprovalStatus::APPROVED->value;
        $qb->from('ClosingRequest')
                ->innerJoin('ClosingRequest', 'CustomerAssignment', 'CustomerAssignment',
                        "ClosingRequest.CustomerAssignment_id = CustomerAssignment.id")
                ->andWhere($qb->expr()->eq('ClosingRequest.status', "'$approvedClosingRequestStatus'"))
                ->andWhere($qb->expr()->eq('CustomerAssignment.Sales_id', ':salesId'))
                ->setParameter('salesId', $salesId);
        $this->recurrenceType->applyToQuery($qb, 'ClosingRequest.createdTime', $this->recurrenceCount);
        $this->evaluationType->applyToQuery($qb, 'ClosingRequest.transactionValue');
    }
}
