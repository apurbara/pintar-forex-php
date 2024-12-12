<?php

namespace Manager\Domain\DependencyModel;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Manager\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesRankRepository;
use Shared\Domain\Enum\CustomerAssignmentStatus;
use Shared\Domain\Enum\EvaluationType;
use Shared\Domain\Enum\ManagementApprovalStatus;
use Shared\Domain\Enum\QueryOrder;
use Shared\Domain\Enum\RecurrenceType;
use Shared\Domain\Enum\SalesMetricType;
use Shared\Domain\Enum\SalesRole;

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

    #[Column(type: "string", enumType: SalesRole::class)]
    protected SalesRole $salesRole;

    #[Column(type: "string", enumType: SalesMetricType::class)]
    protected SalesMetricType $salesMetricType;

    #[Column(type: "string", enumType: EvaluationType::class)]
    protected EvaluationType $evaluationType;

    #[Column(type: "string", enumType: RecurrenceType::class)]
    protected RecurrenceType $recurrenceType;

    #[Column(type: "smallint", nullable: false)]
    protected int $displaySalesNumber;

    #[Column(type: "string", enumType: QueryOrder::class)]
    protected QueryOrder $queryOrder;

    #[Column(type: "string", length: 1024, nullable: true)]
    protected ?string $displaySchema;

    protected function __construct()
    {
        
    }

    //
    public function fetchSummaryResult(Connection $connection, string $managerId): array
    {
        $qb = $connection->createQueryBuilder();
        $qb->select('Sales.id')
                ->addSelect('Sales.name')
                ->from('Sales')
                ->andWhere($qb->expr()->eq('Sales.contractTerminated', 0))
                ->andWhere($qb->expr()->eq('Sales.Manager_id', ':managerId'))
                ->setParameter('managerId', $managerId)
                ->addGroupBy('Sales.id')
                ->setMaxResults($this->displaySalesNumber);
        $this->queryOrder->applyToQuery($qb, 'achievement');

        match ($this->salesMetricType) {
            SalesMetricType::SALES_ACTIVITY => $this->applySalesActivityReportMetric($qb),
            SalesMetricType::SUCCESSFULL_ASSIGNMENT => $this->applySuccessfullAssignmentMetric($qb),
        };

        return [
            'name' => $this->name,
            'result' => $qb->executeQuery()->fetchAllAssociative(),
        ];
    }

    protected function applySalesActivityReportMetric(QueryBuilder $qb): void
    {
        match ($this->salesRole) {
            SalesRole::GREETER => $qb->leftJoin('Sales', 'GreetingAssignment', 'GreetingAssignment',
                            'GreetingAssignment.Sales_id = Sales.id')
                    ->leftJoin('GreetingAssignment', 'CustomerAssignment', 'CustomerAssignment',
                            'GreetingAssignment.CustomerAssignment_id = CustomerAssignment.id'),
            SalesRole::FACT_FINDER => $qb->leftJoin('Sales', 'FactFindingAssignment', 'FactFindingAssignment',
                            'FactFindingAssignment.Sales_id = Sales.id')
                    ->leftJoin('FactFindingAssignment', 'CustomerAssignment', 'CustomerAssignment',
                            'FactFindingAssignment.CustomerAssignment_id = CustomerAssignment.id'),
            SalesRole::STRIKER => $qb->leftJoin('Sales', 'StrikingAssignment', 'StrikingAssignment',
                            'StrikingAssignment.Sales_id = Sales.id')
                    ->leftJoin('StrikingAssignment', 'CustomerAssignment', 'CustomerAssignment',
                            'StrikingAssignment.CustomerAssignment_id = CustomerAssignment.id'),
        };
        $qb->leftJoin('CustomerAssignment', 'SalesActivitySchedule', 'SalesActivitySchedule',
                        'SalesActivitySchedule.CustomerAssignment_id = CustomerAssignment.id')
                ->leftJoin('SalesActivitySchedule', 'SalesActivityReport', 'SalesActivityReport',
                        'SalesActivityReport.SalesActivitySchedule_id = SalesActivitySchedule.id');
        $this->recurrenceType->applyToQuery($qb, 'SalesActivityReport.submitTime', 1);
        $this->evaluationType->applyToQuery($qb, 'SalesActivityReport.id');
    }

    //
    protected function applySuccessfullAssignmentMetric(QueryBuilder $qb): void
    {
        match ($this->salesRole) {
            SalesRole::GREETER => $this->applySuccessfullGreetingMetric($qb),
            SalesRole::FACT_FINDER => $this->applySuccessfullFactFindingMetric($qb),
            SalesRole::STRIKER => $this->applyApprovedClosingRequestMetric($qb),
        };
    }

    private function applySuccessfullGreetingMetric(QueryBuilder $qb): void
    {
        $completedAssignmentValue = CustomerAssignmentStatus::COMPLETED->value;
        $qb->leftJoin('Sales', 'GreetingAssignment', 'GreetingAssignment',
                        "GreetingAssignment.Sales_id = Sales.id AND GreetingAssignment.status = '{$completedAssignmentValue}'")
                ->leftJoin('GreetingAssignment', 'CustomerAssignment', 'CustomerAssignment',
                        'GreetingAssignment.CustomerAssignment_id = CustomerAssignment.id');
        $this->recurrenceType->applyToQuery($qb, 'CustomerAssignment.completedTime', 1);
        $this->evaluationType->applyToQuery($qb, 'GreetingAssignment.id');
    }

    private function applySuccessfullFactFindingMetric(QueryBuilder $qb): void
    {
        $completedAssignmentValue = CustomerAssignmentStatus::COMPLETED->value;
        $qb->leftJoin('Sales', 'FactFindingAssignment', 'FactFindingAssignment',
                        "FactFindingAssignment.Sales_id = Sales.id AND FactFindingAssignment.status = '{$completedAssignmentValue}'")
                ->leftJoin('FactFindingAssignment', 'CustomerAssignment', 'CustomerAssignment',
                        'FactFindingAssignment.CustomerAssignment_id = CustomerAssignment.id');
        $this->recurrenceType->applyToQuery($qb, 'CustomerAssignment.completedTime', 1);
        $this->evaluationType->applyToQuery($qb, 'FactFindingAssignment.id');
    }

    private function applyApprovedClosingRequestMetric(QueryBuilder $qb): void
    {
        $approvedClosingRequestStatus = ManagementApprovalStatus::APPROVED->value;
        $qb->leftJoin('Sales', 'StrikingAssignment', 'StrikingAssignment', 'StrikingAssignment.Sales_id = Sales.id')
                ->leftJoin('StrikingAssignment', 'ClosingRequest', 'ClosingRequest',
                        "ClosingRequest.StrikingAssignment_id = StrikingAssignment.id AND ClosingRequest.status = '{$approvedClosingRequestStatus}'");
        $this->recurrenceType->applyToQuery($qb, 'ClosingRequest.createdTime', 1);
        $this->evaluationType->applyToQuery($qb, 'ClosingRequest.transactionValue');
    }
}
