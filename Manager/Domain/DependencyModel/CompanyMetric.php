<?php

namespace Manager\Domain\DependencyModel;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Manager\Infrastructure\Persistence\Doctrine\Repository\DoctrineCompanyMetricRepository;
use Shared\Domain\Enum\EvaluationType;
use Shared\Domain\Enum\ManagementApprovalStatus;
use Shared\Domain\Enum\MetricType;
use Shared\Domain\Enum\RecurrenceType;

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

    protected function __construct()
    {
        
    }

    //
    public function fetchSummaryResult(Connection $connection, string $managerId): array
    {
        $qb = $connection->createQueryBuilder();
        match ($this->metricType) {
            MetricType::SALES_ACTIVITY_REPORT => $this->applySalesActivityReportMetric($qb, $managerId),
            MetricType::APPROVED_CLOSING_REQUEST => $this->applyApprovedClosingRequestMetric($qb, $managerId)
        };

        return [
            'name' => $this->name,
            'target' => $this->target,
            'result' => $qb->executeQuery()->fetchAllAssociative(),
        ];
    }

    protected function applySalesActivityReportMetric(QueryBuilder $qb, string $managerId): void
    {
        $qb->from('SalesActivityReport')
                ->innerJoin('SalesActivityReport', 'SalesActivitySchedule', 'SalesActivitySchedule',
                        'SalesActivityReport.SalesActivitySchedule_id = SalesActivitySchedule.id')
                ->innerJoin('SalesActivitySchedule', 'CustomerAssignment', 'CustomerAssignment',
                        'SalesActivitySchedule.CustomerAssignment_id = CustomerAssignment.id')
                ->innerJoin('CustomerAssignment', 'Sales', 'Sales', 'CustomerAssignment.Sales_id = Sales.id')
                ->andWhere($qb->expr()->eq('Sales.Manager_id', ':managerId'))
                ->setParameter('managerId', $managerId);
        $this->recurrenceType->applyToQuery($qb, 'SalesActivityReport.submitTime', $this->recurrenceCount);
        $this->evaluationType->applyToQuery($qb, 'SalesActivityReport.id');
    }

    protected function applyApprovedClosingRequestMetric(QueryBuilder $qb, string $managerId): void
    {
        $approvedClosingRequestStatus = ManagementApprovalStatus::APPROVED->value;
        $qb->from('ClosingRequest')
                ->andWhere($qb->expr()->eq('ClosingRequest.status', "'{$approvedClosingRequestStatus}'"))
                ->innerJoin('ClosingRequest', 'CustomerAssignment', 'CustomerAssignment',
                        'ClosingRequest.CustomerAssignment_id = CustomerAssignment.id')
                ->innerJoin('CustomerAssignment', 'Sales', 'Sales', 'CustomerAssignment.Sales_id = Sales.id')
                ->andWhere($qb->expr()->eq('Sales.Manager_id', ':managerId'))
                ->setParameter('managerId', $managerId);
        $this->recurrenceType->applyToQuery($qb, 'ClosingRequest.createdTime', $this->recurrenceCount);
        $this->evaluationType->applyToQuery($qb, 'ClosingRequest.transactionValue');
    }
}
