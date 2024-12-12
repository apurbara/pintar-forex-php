<?php

namespace Manager\Domain\DependencyModel;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Manager\Infrastructure\Persistence\Doctrine\Repository\DoctrineCompanyMetricRepository;
use Shared\Domain\Enum\EvaluationType;
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
        $this->metricType->applyToQuery($qb, $this->recurrenceType, $this->evaluationType, $this->recurrenceCount);
        match ($this->metricType) {
            MetricType::GREETING_ACTIVITY_REPORT, MetricType::SUCCESSFULL_GREETING => $qb->innerJoin('GreetingAssignment',
                    'Sales', 'Sales', 'GreetingAssignment.Sales_id = Sales.id AND Sales.Manager_id = :managerId'),
            MetricType::FACT_FINDING_ACTIVITY_REPORT, MetricType::SUCCESSFULL_FACT_FINDING => $qb->innerJoin('FactFindingAssignment',
                    'Sales', 'Sales', 'FactFindingAssignment.Sales_id = Sales.id AND Sales.Manager_id = :managerId'),
            MetricType::STRIKING_ACTIVITY_REPORT, MetricType::APPROVED_CLOSING_REQUEST => $qb->innerJoin('StrikingAssignment',
                    'Sales', 'Sales', 'StrikingAssignment.Sales_id = Sales.id AND Sales.Manager_id = :managerId'),
        };
        $qb->setParameter('managerId', $managerId);

        return [
            'name' => $this->name,
            'target' => $this->target,
            'result' => $qb->executeQuery()->fetchAllAssociative(),
        ];
    }
}
