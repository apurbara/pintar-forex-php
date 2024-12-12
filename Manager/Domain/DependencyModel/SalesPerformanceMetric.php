<?php

namespace Manager\Domain\DependencyModel;

use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Manager\Domain\DependencyModel\SalesPerformanceMetric\SalesPerformanceMetricEvaluation;
use Manager\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesPerformanceMetricRepository;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObjectList;
use Resources\Infrastructure\GraphQL\Attributes\IncludeAsInputList;
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
        $this->salesPerformanceMetricType->applyToQuery($salesSubquery, $this->recurrenceType, $this->recurrenceCount);

        $qb = $connection->createQueryBuilder();
        $qb->from(sprintf('(%s)', $salesSubquery->getSQL()), 'salesPerformance')
                ->addSelect('salesPerformance.evaluationTime')
                ->addGroupBy('salesPerformance.evaluationTime');
        match ($this->recurrenceType){
            RecurrenceType::ONCE=> $qb->innerJoin('salesPerformance', 'Sales', 'Sales', "salesPerformance.id = Sales.id AND Sales.Manager_id = :managerId AND Sales.contractTerminated = 0"),
            RecurrenceType::DAILY=> $qb->innerJoin('salesPerformance', 'Sales', 'Sales', "salesPerformance.id = Sales.id AND Sales.Manager_id = :managerId AND (salesPerformance.evaluationTime BETWEEN DATE_FORMAT(Sales.createdTime, '%Y-%m-%d') AND DATE_FORMAT(COALESCE(Sales.contractTerminatedTime, NOW()), '%Y-%m-%d'))"),
            RecurrenceType::WEEKLY=> $qb->innerJoin('salesPerformance', 'Sales', 'Sales', "salesPerformance.id = Sales.id AND Sales.Manager_id = :managerId AND (salesPerformance.evaluationTime BETWEEN DATE_FORMAT(Sales.createdTime, '%Y-%u') AND DATE_FORMAT(COALESCE(Sales.contractTerminatedTime, NOW()), '%Y-%u'))"),
            RecurrenceType::MONTHLY => $qb->innerJoin('salesPerformance', 'Sales', 'Sales', "salesPerformance.id = Sales.id AND Sales.Manager_id = :managerId AND (salesPerformance.evaluationTime BETWEEN DATE_FORMAT(Sales.createdTime, '%Y-%m') AND DATE_FORMAT(COALESCE(Sales.contractTerminatedTime, NOW()), '%Y-%m'))"),
            RecurrenceType::YEARLY=> $qb->innerJoin('salesPerformance', 'Sales', 'Sales', "salesPerformance.id = Sales.id AND Sales.Manager_id = :managerId AND (salesPerformance.evaluationTime BETWEEN DATE_FORMAT(Sales.createdTime, '%Y') AND DATE_FORMAT(COALESCE(Sales.contractTerminatedTime, NOW()), '%Y'))"),
        };
        $qb->setParameter('managerId', $managerId);

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
}
