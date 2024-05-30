<?php

namespace Company\Domain\Model\SalesPerformanceMetric;

use Company\Domain\Model\SalesPerformanceMetric;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesPerformanceMetricEvaluationRepository;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use SharedContext\Domain\Enum\EvaluationType;

#[Entity(repositoryClass: DoctrineSalesPerformanceMetricEvaluationRepository::class)]
class SalesPerformanceMetricEvaluation
{

    #[ManyToOne(targetEntity: SalesPerformanceMetric::class, inversedBy: "evaluations", fetch: "LAZY")]
    #[JoinColumn(name: "SalesPerformanceMetric_id", referencedColumnName: "id")]
    protected SalesPerformanceMetric $salesPerformanceMetric;

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $removed;

    #[Column(type: "string", length: 255, nullable: false)]
    protected string $alias;

    #[Column(type: "string", enumType: EvaluationType::class)]
    protected EvaluationType $evaluationType;

    public function isRemoved(): bool
    {
        return $this->removed;
    }

    public function applyToQuery(QueryBuilder $qb, string $metricEvaluationColumn): void
    {
        $this->evaluationType->applyToQuery($qb, $metricEvaluationColumn, $this->alias);
    }
}
