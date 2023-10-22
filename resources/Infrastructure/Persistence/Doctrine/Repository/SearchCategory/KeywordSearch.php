<?php

namespace Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory;

use Doctrine\DBAL\Query\QueryBuilder;

class KeywordSearch
{

    protected ?string $value;
    protected ?array $columns;
    protected ?string $comparisonType = 'LIKE';

    public function __construct(?string $value = null, ?array $columns = [], ?string $comparisonType = 'LIKE')
    {
        $this->value = $value;
        $this->columns = $columns;
        $this->comparisonType = $comparisonType;
    }

    public static function fromSchema(array $schema = []): static
    {
        return new static($schema['value'] ?? null, $schema['columns'] ?? [], $schema['comparisonType'] ?? 'LIKE');
    }

    public function applyToQuery(QueryBuilder $qb): void
    {
        if (empty($this->value) || empty($this->columns) || empty($this->comparisonType)) {
            return;
        }

        $args = [];
        foreach ($this->columns as $column) {
            $args[] = match ($this->comparisonType) {
                'EQ' => $qb->expr()->eq($column, ':keyword'),
                'GT' => $qb->expr()->gt($column, ':keyword'),
                'GTE' => $qb->expr()->gte($column, ':keyword'),
                'LT' => $qb->expr()->lt($column, ':keyword'),
                'LTE' => $qb->expr()->lte($column, ':keyword'),
                default => $qb->expr()->like($column, ':keyword'),
            };
        }
        if (!empty($args)) {
            $qb->andWhere($qb->expr()->or(...$args))
                    ->setParameter('keyword', $this->value);
        }
    }
}
