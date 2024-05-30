<?php

namespace Company\Domain\Model;

use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesRankRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use SharedContext\Domain\Enum\EvaluationType;
use SharedContext\Domain\Enum\ManagementApprovalStatus;
use SharedContext\Domain\Enum\MetricType;
use SharedContext\Domain\Enum\QueryOrder;
use SharedContext\Domain\Enum\RecurrenceType;

#[Entity(repositoryClass: DoctrineSalesRankRepository::class)]
class SalesRank
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

    #[Column(type: "string", enumType: MetricType::class)]
    protected MetricType $metricType;

    #[Column(type: "string", enumType: EvaluationType::class)]
    protected EvaluationType $evaluationType;

    #[Column(type: "string", enumType: RecurrenceType::class)]
    protected RecurrenceType $recurrenceType;

    #[Column(type: "smallint", nullable: true)]
    protected ?int $displaySalesNumber;

    #[Column(type: "string", enumType: QueryOrder::class)]
    protected QueryOrder $order;

    #[Column(type: "string", length: 1024, nullable: true)]
    protected ?string $displaySchema;

    public function fetchSummaryResult(Connection $connection): array
    {
        $qb = $connection->createQueryBuilder();
        $qb->select('Sales.id')
                ->addSelect('Personnel.name')
                ->from('Sales')
                ->innerJoin('Sales', 'Personnel', 'Personnel', 'Sales.Personnel_id = Personnel.id')
                ->addGroupBy('Sales.id')
                ->setMaxResults($this->displaySalesNumber);
        $this->order->applyToQuery($qb, 'achievement');
        
        match ($this->metricType) {
            MetricType::SALES_ACTIVITY_REPORT => $this->applySalesActivityReportMetric($qb),
            MetricType::APPROVED_CLOSING_REQUEST => $this->applyApprovedClosingRequestMetric($qb),
        };

        return [
            'name' => $this->name,
            'result' => $qb->executeQuery()->fetchAllAssociative(),
        ];
    }

    protected function applySalesActivityReportMetric(QueryBuilder $qb): void
    {
        $qb->leftJoin('Sales', 'CustomerAssignment', 'CustomerAssignment', 'CustomerAssignment.Sales_id = Sales.id')
                ->leftJoin('CustomerAssignment', 'SalesActivitySchedule', 'SalesActivitySchedule',
                        'SalesActivitySchedule.CustomerAssignment_id = CustomerAssignment.id')
                ->leftJoin('SalesActivitySchedule', 'SalesActivityReport', 'SalesActivityReport',
                        'SalesActivityReport.SalesActivitySchedule_id = SalesActivitySchedule.id');
        $this->recurrenceType->applyToQuery($qb, 'SalesActivityReport.submitTime', 1);
        $this->evaluationType->applyToQuery($qb, 'SalesActivityReport.id');
    }

    protected function applyApprovedClosingRequestMetric(QueryBuilder $qb): void
    {
        $approvedClosingRequestStatus = ManagementApprovalStatus::APPROVED->value;
        $qb->leftJoin('Sales', 'CustomerAssignment', 'CustomerAssignment',
                        'CustomerAssignment.Sales_id = Sales.id')
                ->leftJoin('CustomerAssignment', 'ClosingRequest', 'ClosingRequest',
                        "ClosingRequest.CustomerAssignment_id = CustomerAssignment.id AND ClosingRequest.status = '{$approvedClosingRequestStatus}'");
        $this->recurrenceType->applyToQuery($qb, 'ClosingRequest.createdTime', 1);
        $this->evaluationType->applyToQuery($qb, 'ClosingRequest.transactionValue');
    }
}
