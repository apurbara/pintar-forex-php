<?php

namespace Resources\Domain\TaskPayload;

use Resources\Infrastructure\Persistence\PaginationListCategoryInterface;

readonly class ViewPaginationListPayload
{

    public array $result;

    /**
     * 
     * @param array $paginationSchema = [
     *      'keywordSearch' => ['columns' => ['col1', 'Table.col2'], 'value' => '%name%', 'comparisonValue' => 'LIKE'],
     *      'filters' => [
     *          ['column' => 'Table.col1', 'value' => 'value', 'comparisonType' => 'EQ'],
     *          ['column' => 'Table.col2', 'value' => 'value', 'comparisonType' => 'EQ'],
     *      ],
     *      'offsetLimit' => [
     *          'page' => 1,
     *          'pageSize' => 20,
     *          'orders' => [
     *              ['column' => 'Table.col1', 'direction' => 'ASC'],
     *              ['column' => 'Table.col2', 'direction' => 'DESC'],
     *          ],
     *      ],
     *      'cursorLimit' => [
     *          'pageSize' => 20,
     *          'cursor' => 'based64 string represent next records to retrieve ',
     *          'orders' => [
     *              ['column' => 'Table.col1', 'direction' => 'ASC'],
     *              ['column' => 'Table.col2', 'direction' => 'DESC'],
     *          ],
     *      ],
     * ]
     */
    public function __construct(public array $paginationSchema)
    {
        
    }

    public function setResult(array $result)
    {
        $this->result = $result;
    }
}
