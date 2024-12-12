<?php

namespace Manager\Domain\DependencyModel;

use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Manager\Domain\DependencyModel\SalesPerformanceMetric\SalesPerformanceMetricEvaluation;
use Manager\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesPerformanceMetricRepository;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObjectList;
use Resources\Infrastructure\GraphQL\Attributes\IncludeAsInputList;
use Shared\Domain\Enum\ManagementApprovalStatus;
use Shared\Domain\Enum\RecurrenceType;
use Shared\Domain\Enum\SalesPerformanceMetricType;

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
    protected SalesPerformanceMetricType $salesPerformanceMetricType;

    #[Column(type: "string", enumType: RecurrenceType::class)]
    protected RecurrenceType $recurrenceType;

    #[Column(type: "smallint", nullable: true)]
    protected ?int $recurrenceCount;

    #[Column(type: "string", length: 1024, nullable: true)]
    protected ?string $displaySchema;

    #[FetchableObjectList(targetEntity: SalesPerformanceMetricEvaluation::class,
                joinColumnName: 'SalesPerformanceMetric_id')]
    #[IncludeAsInputList(targetEntity: SalesPerformanceMetricEvaluation::class)]
    #[OneToMany(targetEntity: SalesPerformanceMetricEvaluation::class, mappedBy: "salesPerformanceMetric",
                cascade: ["persist"], fetch: "EXTRA_LAZY")]
    protected Collection $evaluations;

    protected function __construct()
    {
    }

    //
    public function fetchSummaryResult(Connection $connection, string $managerId): array
    {
        $salesSubquery = $connection->createQueryBuilder();
        match ($this->metricType) {
            SalesPerformanceMetricType::GREETING_ACTIVITY_REPORT_COUNT => $this->applySalesActivityReportMetric($salesSubquery),
            SalesPerformanceMetricType::APPROVED_CLOSING_REQUEST_SUM => $this->applyApprovedClosingRequestSumMetric($salesSubquery),
            SalesPerformanceMetricType::APPROVED_CLOSING_REQUEST_COUNT => $this->applyApprovedClosingRequestCountMetric($salesSubquery)
        };

        $qb = $connection->createQueryBuilder();
        $qb->from(sprintf('(%s)', $salesSubquery->getSQL()), 'salesPerformance')
                ->setParameter('managerId', $managerId)
                ->addSelect('salesPerformance.evaluationTime')
                ->addGroupBy('salesPerformance.evaluationTime');
        match ($this->recurrenceType){
            RecurrenceType::ONCE=> $qb->innerJoin('salesPerformance', 'Sales', 'Sales', "salesPerformance.id = Sales.id AND Sales.contractTerminated = 0"),
            RecurrenceType::DAILY=> $qb->innerJoin('salesPerformance', 'Sales', 'Sales', "salesPerformance.id = Sales.id AND (salesPerformance.evaluationTime BETWEEN DATE_FORMAT(Sales.createdTime, '%Y-%m-%d') AND DATE_FORMAT(COALESCE(Sales.contractTerminatedTime, NOW()), '%Y-%m-%d'))"),
            RecurrenceType::WEEKLY=> $qb->innerJoin('salesPerformance', 'Sales', 'Sales', "salesPerformance.id = Sales.id AND (salesPerformance.evaluationTime BETWEEN DATE_FORMAT(Sales.createdTime, '%Y-%u') AND DATE_FORMAT(COALESCE(Sales.contractTerminatedTime, NOW()), '%Y-%u'))"),
            RecurrenceType::MONTHLY => $qb->innerJoin('salesPerformance', 'Sales', 'Sales', "salesPerformance.id = Sales.id AND (salesPerformance.evaluationTime BETWEEN DATE_FORMAT(Sales.createdTime, '%Y-%m') AND DATE_FORMAT(COALESCE(Sales.contractTerminatedTime, NOW()), '%Y-%m'))"),
            RecurrenceType::YEARLY=> $qb->innerJoin('salesPerformance', 'Sales', 'Sales', "salesPerformance.id = Sales.id AND (salesPerformance.evaluationTime BETWEEN DATE_FORMAT(Sales.createdTime, '%Y') AND DATE_FORMAT(COALESCE(Sales.contractTerminatedTime, NOW()), '%Y'))"),
        };

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
                ->andWhere($salesSubquery->expr()->eq('Sales.Manager_id', ':managerId'))
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
                ->andWhere($salesSubquery->expr()->eq('Sales.Manager_id', ':managerId'))
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
                ->andWhere($salesSubquery->expr()->eq('Sales.Manager_id', ':managerId'))
                ->leftJoin('Sales', 'CustomerAssignment', 'CustomerAssignment', 'CustomerAssignment.Sales_id = Sales.id')
                ->leftJoin('CustomerAssignment', 'ClosingRequest', 'ClosingRequest',
                        "ClosingRequest.CustomerAssignment_id = CustomerAssignment.id AND ClosingRequest.status = '{$approvedClosingRequestStatus}'")
                ->addGroupBy('Sales.id');
        $this->recurrenceType->applyToQuery($salesSubquery, 'ClosingRequest.createdTime', $this->recurrenceCount);
    }
}
