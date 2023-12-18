<?php

namespace Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory;

use Doctrine\DBAL\Query\QueryBuilder;

class Filter
{

    protected string|array $value;
    protected string $column;
    protected ?string $comparisonType;

    public function __construct(mixed $value, string $column, ?string $comparisonType = 'EQ')
    {
        $this->value = $value;
        $this->column = $column;
        $this->comparisonType = $comparisonType;
    }

    public static function fromSchema(array $schema = []): static
    {
        return new static($schema['value'], $schema['column'], $schema['comparisonType'] ?? 'EQ');
    }

    public function applyToQuery(QueryBuilder $qb): void
    {
        if (is_null($this->value)) {
            return;
        }

        $params = "";
        if (is_array($this->value)) {
            foreach ($this->value as $value) {
                if (is_bool($value)) {
                    $val = intval($value);
                    $params .= empty($params) ? $val : ", {$val}";
                } else {
                    $params .= empty($params) ? "'{$value}'" : ", '{$value}'";
                }
            }
        }
        if ($this->comparisonType == 'IN' && $params) {
            $qb->andWhere($qb->expr()->in($this->column, $params));
            return;
        }

        $cleanColumn = str_replace(array('.'), '', $this->column);
        $comparison = match ($this->comparisonType) {
            'LT' => $qb->expr()->lt($this->column, ":{$cleanColumn}"),
            'LTE' => $qb->expr()->lte($this->column, ":{$cleanColumn}"),
            'GT' => $qb->expr()->gt($this->column, ":{$cleanColumn}"),
            'GTE' => $qb->expr()->gte($this->column, ":{$cleanColumn}"),
            'LIKE' => $qb->expr()->like($this->column, ":{$cleanColumn}"),
            'IN' => $qb->expr()->in($this->column, ":{$cleanColumn}"),
            default => $qb->expr()->eq($this->column, ":{$cleanColumn}"),
        };
        $qb->andWhere($comparison)
            ->setParameter($cleanColumn, implode(', ', (array)$this->value));
    }
}
