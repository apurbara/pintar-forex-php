<?php

namespace Resources\Domain\TaskPayload;

class ViewSummaryPayload
{
    public mixed $result;

    /**
     * 
     * @param array $searchSchema = [
     *      'keywordSearch' => ['columns' => ['col1', 'Table.col2'], 'value' => '%name%', 'comparisonValue' => 'LIKE'],
     *      'filters' => [
     *          ['column' => 'Table.col1', 'value' => 'value', 'comparisonType' => 'EQ'],
     *          ['column' => 'Table.col2', 'value' => 'value', 'comparisonType' => 'EQ'],
     *      ],
     * ]
     */
    public function __construct(public array $searchSchema)
    {
        
    }

    public function setResult(mixed $result)
    {
        $this->result = $result;
    }
}
