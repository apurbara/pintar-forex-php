<?php

namespace Resources\Domain\TaskPayload;

readonly class ViewAllListPayload
{

    public array $result;

    /**
     * 
     * @param array $listSchema = [
     *      'keywordSearch' => ['columns' => ['col1', 'Table.col2'], 'value' => '%name%', 'comparisonValue' => 'LIKE'],
     *      'filters' => [
     *          ['column' => 'Table.col1', 'value' => 'value', 'comparisonType' => 'EQ'],
     *          ['column' => 'Table.col2', 'value' => 'value', 'comparisonType' => 'EQ'],
     *      ],
     * ]
     */
    public function __construct(public array $listSchema)
    {
        
    }

    public function setResult(array $result)
    {
        $this->result = $result;
    }
}
