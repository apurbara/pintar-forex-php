<?php

namespace Company\Domain\Model\SalesPerformanceMetric;

use Company\Domain\Model\SalesPerformanceMetric;
use Company\Domain\Model\SalesPerformanceMetricData;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesPerformanceMetricEvaluationRepository;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Resources\ValidationRule;
use Resources\ValidationService;
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

    private function setAlias(?string $alias): void
    {
        ValidationService::build()
                ->addRule(ValidationRule::notEmpty())
                ->execute($alias, 'evaluation alias is mandatory');
        $this->alias = $alias;
    }

    public function __construct(SalesPerformanceMetric $salesPerformanceMetric, string $id,
            SalesPerformanceMetricEvaluationData $data)
    {
        $this->salesPerformanceMetric = $salesPerformanceMetric;
        $this->id = $id;
        $this->removed = false;
        $this->setAlias($data->alias);
        $this->evaluationType = EvaluationType::from($data->evaluationType);
    }

    public function update(SalesPerformanceMetricData $salesPerformanceMetricData): void
    {
        $data = $salesPerformanceMetricData->pullEvaluationDataAssociationWithType($this->evaluationType->value);
        if ($data) {
            $this->setAlias($data->alias);
            $this->removed = false;
        } else {
            $this->removed = true;
        }
    }

    public function isRemoved(): bool
    {
        return $this->removed;
    }

    public function applyToQuery(QueryBuilder $qb, string $metricEvaluationColumn): void
    {
        $this->evaluationType->applyToQuery($qb, $metricEvaluationColumn, $this->alias);
    }
}
