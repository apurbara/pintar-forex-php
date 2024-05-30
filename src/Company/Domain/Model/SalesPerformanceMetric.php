<?php

namespace Company\Domain\Model;

use Company\Domain\Model\SalesPerformanceMetric\SalesPerformanceMetricEvaluation;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesPerformanceMetricRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use SharedContext\Domain\Enum\ManagementApprovalStatus;
use SharedContext\Domain\Enum\RecurrenceType;
use SharedContext\Domain\Enum\SalesPerformanceMetricType;

#[Entity(repositoryClass: DoctrineSalesPerformanceMetricRepository::class)]
class SalesPerformanceMetric
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

    #[Column(type: "string", enumType: SalesPerformanceMetricType::class)]
    protected SalesPerformanceMetricType $metricType;

    #[Column(type: "string", enumType: RecurrenceType::class)]
    protected RecurrenceType $recurrenceType;

    #[Column(type: "smallint", nullable: true)]
    protected ?int $recurrenceCount;

    #[Column(type: "string", length: 1024, nullable: true)]
    protected ?string $displaySchema;

    #[OneToMany(targetEntity: SalesPerformanceMetricEvaluation::class, mappedBy: "salesPerformanceMetric",
                cascade: ["persist"], fetch: "EXTRA_LAZY")]
    protected Collection $evaluations;

    public function fetchSummaryResult(Connection $connection): array
    {
        $salesSubquery = $connection->createQueryBuilder();
        match ($this->metricType) {
            SalesPerformanceMetricType::SALES_ACTIVITY_REPORT => $this->applySalesActivityReportMetric($salesSubquery),
            SalesPerformanceMetricType::APPROVED_CLOSING_REQUEST_SUM => $this->applyApprovedClosingRequestSumMetric($salesSubquery),
            SalesPerformanceMetricType::APPROVED_CLOSING_REQUEST_COUNT => $this->applyApprovedClosingRequestCountMetric($salesSubquery)
        };

        $qb = $connection->createQueryBuilder();
        $qb->from(sprintf('(%s)', $salesSubquery->getSQL()), 'salesPerformance')
                ->addSelect('salesPerformance.evaluationTime')
                ->addGroupBy('salesPerformance.evaluationTime');

        $criteria = Criteria::create()
                ->andWhere(Criteria::expr()->eq('removed', false));
        foreach ($this->evaluations->matching($criteria)->getIterator() as $salesPerformanceMetricEvaluation) {
            $salesPerformanceMetricEvaluation->applyToQuery($qb, 'salesPerformance.achievement');
        }

        return [
            'name' => $this->name,
            'result' => $qb->executeQuery()->fetchAllAssociative(),
        ];
    }

    protected function applySalesActivityReportMetric(QueryBuilder $salesSubquery): void
    {
        $salesSubquery->select("COUNT(SalesActivityReport.id) achievement")
                ->addSelect('Sales.id')
                ->from('Sales')
                ->leftJoin('Sales', 'CustomerAssignment', 'CustomerAssignment', 'CustomerAssignment.Sales_id = Sales.id')
                ->leftJoin('CustomerAssignment', 'SalesActivitySchedule', 'SalesActivitySchedule',
                        'SalesActivitySchedule.CustomerAssignment_id = CustomerAssignment.id')
                ->leftJoin('SalesActivitySchedule', 'SalesActivityReport', 'SalesActivityReport',
                        'SalesActivityReport.SalesActivitySchedule_id = SalesActivitySchedule.id')
                ->addGroupBy('Sales.id');
        $this->recurrenceType->applyToQuery($salesSubquery, 'SalesActivityReport.submitTime', $this->recurrenceCount);
    }

    protected function applyApprovedClosingRequestSumMetric(QueryBuilder $salesSubquery): void
    {
        $approvedClosingRequestStatus = ManagementApprovalStatus::APPROVED->value;
        $salesSubquery->select("SUM(ClosingRequest.transactionValue) achievement")
                ->addSelect('Sales.id')
                ->from('Sales')
                ->leftJoin('Sales', 'CustomerAssignment', 'CustomerAssignment', 'CustomerAssignment.Sales_id = Sales.id')
                ->leftJoin('CustomerAssignment', 'ClosingRequest', 'ClosingRequest',
                        "ClosingRequest.CustomerAssignment_id = CustomerAssignment.id AND ClosingRequest.status = '{$approvedClosingRequestStatus}'")
                ->addGroupBy('Sales.id');
        $this->recurrenceType->applyToQuery($salesSubquery, 'ClosingRequest.createdTime', $this->recurrenceCount);
    }

    protected function applyApprovedClosingRequestCountMetric(QueryBuilder $salesSubquery): void
    {
        $approvedClosingRequestStatus = ManagementApprovalStatus::APPROVED->value;
        $salesSubquery->select("COUNT(ClosingRequest.transactionValue) achievement")
                ->addSelect('Sales.id')
                ->from('Sales')
                ->leftJoin('Sales', 'CustomerAssignment', 'CustomerAssignment', 'CustomerAssignment.Sales_id = Sales.id')
                ->leftJoin('CustomerAssignment', 'ClosingRequest', 'ClosingRequest',
                        "ClosingRequest.CustomerAssignment_id = CustomerAssignment.id AND ClosingRequest.status = '{$approvedClosingRequestStatus}'")
                ->addGroupBy('Sales.id');
        $this->recurrenceType->applyToQuery($salesSubquery, 'ClosingRequest.createdTime', $this->recurrenceCount);
    }
}
