<?php

namespace Sales\Domain\DependencyModel;

use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineStrikerMetricRepository;
use Shared\Domain\Enum\EvaluationType;
use Shared\Domain\Enum\ManagementApprovalStatus;
use Shared\Domain\Enum\RecurrenceType;
use Shared\Domain\Enum\SalesMetricType;

#[Entity(repositoryClass: DoctrineStrikerMetricRepository::class)]
class StrikerMetric
{

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $disabled;

    #[Column(type: "string", length: 255, nullable: false)]
    protected string $name;

    #[Column(type: "integer", nullable: true)]
    protected int $target;

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

    protected function __construct()
    {
        
    }
    
    public function fetchSummaryResult(Connection $connection, string $salesId): array
    {
        $qb = $connection->createQueryBuilder();
        match ($this->salesMetricType) {
            SalesMetricType::SALES_ACTIVITY => $this->applySalesActivityReportMetric($qb, $salesId),
            SalesMetricType::SUCCESSFULL_ASSIGNMENT => $this->applySuccessfullStrikingMetric($qb, $salesId),
        };
        $result = [
            'name' => $this->name,
            'target' => $this->target,
            'dailyReminderTarget' => $this->dailyReminderTarget,
            'result' => $qb->executeQuery()->fetchAllAssociative(),
        ];
        
        if (!empty($this->dailyReminderTarget)) {
            $dailyAchievementQB = $connection->createQueryBuilder();
            match ($this->salesMetricType) {
                SalesMetricType::SALES_ACTIVITY => $this->applySalesActivityDailyAchievement($dailyAchievementQB, $salesId),
                SalesMetricType::SUCCESSFULL_ASSIGNMENT => $this->applySuccessfullStrikingDailyAchievement($dailyAchievementQB, $salesId),
            };
            $result['dailyAchievement'] = $dailyAchievementQB->executeQuery()->fetchAssociative()['achievement'];
        }
        return $result;

    }
    
    protected function prepareSalesActivityReportMetric(QueryBuilder $qb, string $salesId): void
    {
        $qb->from('SalesActivityReport')
                ->innerJoin('SalesActivityReport', 'SalesActivitySchedule', 'SalesActivitySchedule',
                        'SalesActivityReport.SalesActivitySchedule_id = SalesActivitySchedule.id')
                ->innerJoin('SalesActivitySchedule', 'CustomerAssignment', 'CustomerAssignment',
                        'SalesActivitySchedule.CustomerAssignment_id = CustomerAssignment.id')
                ->innerJoin('CustomerAssignment', 'StrikingAssignment', 'StrikingAssignment',
                        'StrikingAssignment.CustomerAssignment_id = CustomerAssignment.id')
                ->andWhere($qb->expr()->eq('StrikingAssignment.Sales_id', ':salesId'))
                ->setParameter('salesId', $salesId);
        $this->evaluationType->applyToQuery($qb, 'SalesActivityReport.id');
    }
    
    protected function applySalesActivityReportMetric(QueryBuilder $qb, string $salesId): void
    {
        $this->prepareSalesActivityReportMetric($qb, $salesId);
        $this->recurrenceType->applyToQuery($qb, 'SalesActivityReport.submitTime', $this->recurrenceCount);
    }
    
    protected function applySalesActivityDailyAchievement(QueryBuilder $qb, string $salesId): void
    {
        $this->prepareSalesActivityReportMetric($qb, $salesId);
        $qb->setMaxResults(1);

        $currentDate = (new DateTime())->format('Ymd');
        $qb->andWhere($qb->expr()->eq("DATE_FORMAT(SalesActivityReport.submitTime, '%Y%m%d')", $currentDate));
    }

    //
    protected function prepareSuccessfullStrikingMetric(QueryBuilder $qb, string $salesId): void
    {
        $approvedClosingRequest = ManagementApprovalStatus::APPROVED->value;
        $qb->from('ClosingRequest')
                ->innerJoin('ClosingRequest', 'StrikingAssignment', 'StrikingAssignment', 'ClosingRequest.StrikingAssignment_id = StrikingAssignment.id')
                ->andWhere($qb->expr()->eq('StrikingAssignment.Sales_id', ':salesId'))
                ->andWhere($qb->expr()->eq('ClosingRequest.status', "'$approvedClosingRequest'"))
                ->setParameter('salesId', $salesId);
        $this->evaluationType->applyToQuery($qb, 'ClosingRequest.transactionValue');
    }
    protected function applySuccessfullStrikingMetric(QueryBuilder $qb, string $salesId): void
    {
        $this->prepareSuccessfullStrikingMetric($qb, $salesId);
        $this->recurrenceType->applyToQuery($qb, 'ClosingRequest.createdTime', $this->recurrenceCount);
    }
    protected function applySuccessfullStrikingDailyAchievement(QueryBuilder $qb, string $salesId): void
    {
        $this->prepareSuccessfullStrikingMetric($qb, $salesId);
        $qb->setMaxResults(1);

        $currentDate = (new DateTime())->format('Ymd');
        $qb->andWhere($qb->expr()->eq("DATE_FORMAT(ClosingRequest.createdTime, '%Y%m%d')", $currentDate));
    }
}
