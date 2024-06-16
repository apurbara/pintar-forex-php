<?php

namespace Company\Domain\Model;

use Company\Domain\Model\SalesPerformanceMetric\SalesPerformanceMetricEvaluation;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesPerformanceMetricRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Resources\Exception\RegularException;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObjectList;
use Resources\Infrastructure\GraphQL\Attributes\IncludeAsInputList;
use Resources\Uuid;
use Resources\ValidationRule;
use Resources\ValidationService;
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

    #[FetchableObjectList(targetEntity: SalesPerformanceMetricEvaluation::class,
                joinColumnName: 'SalesPerformanceMetric_id')]
    #[IncludeAsInputList(targetEntity: SalesPerformanceMetricEvaluation::class)]
    #[OneToMany(targetEntity: SalesPerformanceMetricEvaluation::class, mappedBy: "salesPerformanceMetric",
                cascade: ["persist"], fetch: "EXTRA_LAZY")]
    protected Collection $evaluations;

    private function setName(?string $name): void
    {
        ValidationService::build()
                ->addRule(ValidationRule::notEmpty())
                ->execute($name, 'name is mandatory');
        $this->name = $name;
    }

    private function addEvaluation(SalesPerformanceMetricData $data): void
    {
        foreach ($data->getEvaluations() as $evaluationData) {
            $evaluation = new SalesPerformanceMetricEvaluation($this, Uuid::generateUuid4(), $evaluationData);
            $this->evaluations->add($evaluation);
        }
    }

    private function assertEvaluationExist(): void
    {
        $criteria = Criteria::create()
                ->andWhere(Criteria::expr()->eq('removed', false));
        if (empty($this->evaluations->matching($criteria)->count())) {
            throw RegularException::badRequest('at least one evaluation is required');
        }
    }

    public function __construct(string $id, SalesPerformanceMetricData $data)
    {
        $this->id = $id;
        $this->disabled = false;
        $this->createdTime = new DateTimeImmutable();
        $this->lastModifiedTime = new DateTimeImmutable();
        $this->setName($data->name);
        $this->metricType = SalesPerformanceMetricType::from($data->metricType);
        $this->recurrenceType = RecurrenceType::from($data->recurrenceType);
        $this->recurrenceCount = $data->recurrenceCount;
        $this->displaySchema = $data->displaySchema;
        //
        $this->evaluations = new ArrayCollection();
        $this->addEvaluation($data);
        $this->assertEvaluationExist();
    }

    public function update(SalesPerformanceMetricData $data): void
    {
        $this->lastModifiedTime = new DateTimeImmutable();
        $this->setName($data->name);
        $this->metricType = SalesPerformanceMetricType::from($data->metricType);
        $this->recurrenceType = RecurrenceType::from($data->recurrenceType);
        $this->recurrenceCount = $data->recurrenceCount;
        $this->displaySchema = $data->displaySchema;

        foreach ($this->evaluations->getIterator() as $evaluation) {
            $evaluation->update($data);
        }
        $this->addEvaluation($data);
        $this->assertEvaluationExist();
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
        match ($this->recurrenceType){
            RecurrenceType::ONCE=> $qb->innerJoin('salesPerformance', 'Sales', 'Sales', "salesPerformance.id = Sales.id AND Sales.cancelled = 0"),
            RecurrenceType::DAILY=> $qb->innerJoin('salesPerformance', 'Sales', 'Sales', "salesPerformance.id = Sales.id AND (salesPerformance.evaluationTime BETWEEN DATE_FORMAT(Sales.createdTime, '%Y-%m-%d') AND DATE_FORMAT(COALESCE(Sales.cancelTime, NOW()), '%Y-%m-%d'))"),
            RecurrenceType::WEEKLY=> $qb->innerJoin('salesPerformance', 'Sales', 'Sales', "salesPerformance.id = Sales.id AND (salesPerformance.evaluationTime BETWEEN DATE_FORMAT(Sales.createdTime, '%Y-%u') AND DATE_FORMAT(COALESCE(Sales.cancelTime, NOW()), '%Y-%u'))"),
            RecurrenceType::MONTHLY => $qb->innerJoin('salesPerformance', 'Sales', 'Sales', "salesPerformance.id = Sales.id AND (salesPerformance.evaluationTime BETWEEN DATE_FORMAT(Sales.createdTime, '%Y-%m') AND DATE_FORMAT(COALESCE(Sales.cancelTime, NOW()), '%Y-%m'))"),
            RecurrenceType::YEARLY=> $qb->innerJoin('salesPerformance', 'Sales', 'Sales', "salesPerformance.id = Sales.id AND (salesPerformance.evaluationTime BETWEEN DATE_FORMAT(Sales.createdTime, '%Y') AND DATE_FORMAT(COALESCE(Sales.cancelTime, NOW()), '%Y'))"),
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
