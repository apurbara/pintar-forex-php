<?php

namespace Resources\Infrastructure\Persistence\Doctrine\Repository\PaginationListCategory;

use Doctrine\DBAL\Query\QueryBuilder;
use Google\Service\Analytics\Column;
use Resources\Infrastructure\Persistence\Doctrine\Repository\PageLimitInterface;

class CursorLimit implements PageLimitInterface
{

    protected int $pageSize;
    protected ?array $cursor;
    protected array $orders;

    public function __construct(
        int $pageSize = 20,
        ?string $cursor = null,
        array $orders = [['column' => 'id', 'direction' => 'ASC']]
    ) {
        $this->pageSize = ($pageSize > 100) ? 100 : $pageSize;
        $this->cursor = isset($cursor) ? json_decode(base64_decode($cursor), true) : null;
        $orderedById = false;
        foreach ($orders as $order) {
            if (!empty($order['column'])) {
                $this->orders[] = ['column' => $order['column'], 'direction' => $order['direction'] ?? 'ASC'];
                $orderedById = $orderedById || ($order['column'] === 'id');
            }
        }
        if (!$orderedById) {
            $this->orders[] = ['column' => 'id', 'direction' => 'ASC'];
        }
    }

    public static function fromSchema(array $schema = []): static
    {
        return new static(
            $schema['pageSize'] ?? 20,
            $schema['cursor'] ?? null,
            $schema['orders'] ?? []
        );
    }

    public function paginateResult(QueryBuilder $qb): array
    {
        $total = $this->getTotalResult(clone $qb);
        $resultList = $this->getResultList($qb);

        $firstElement = null;
        $lastElement = null;
        if (count($resultList) > $this->pageSize) {
            if (isset($this->cursor)) {
                if ($this->cursor['direction'] === 'NEXT') {
                    array_pop($resultList);
                } else {
                    array_shift($resultList);
                }
                $firstElement = $resultList[array_key_first($resultList)];
            } else {
                array_pop($resultList);
            }
            $lastElement = $resultList[array_key_last($resultList)];
        } elseif (!empty($resultList)) {
            if (isset($this->cursor)) {
                if ($this->cursor['direction'] === 'NEXT') {
                    $firstElement = $resultList[array_key_first($resultList)];
                } else {
                    $lastElement = $resultList[array_key_last($resultList)];
                }
            }
        }

        $toNextPage = [];
        $toPreviousPage = [];
        foreach ($this->orders as $order) {
            $colNames = explode(".", $order['column']);
            $colAlias = end($colNames);
            if (isset($lastElement)) {
//                $toNextPage['pointers'][$order['column']] = $lastElement[$order['column']];
                $toNextPage['pointers'][$order['column']] = $lastElement[$colAlias];
            }
            if (isset($firstElement)) {
//                $toPreviousPage['pointers'][$order['column']] = $firstElement[$order['column']];
                $toPreviousPage['pointers'][$order['column']] = $firstElement[$colAlias];
            }
        }
        $cursorToNextPage = !empty($toNextPage) ?
            base64_encode(json_encode(array_merge($toNextPage, ['direction' => 'NEXT']))) : null;
        $cursorToPreviousPage = !empty($toPreviousPage) ?
            base64_encode(json_encode(array_merge($toPreviousPage, ['direction' => 'PREVIOUS']))) : null;
        return [
            'list' => $resultList,
            'cursorLimit' => [
                'pageSize' => $this->pageSize,
                'cursorToNextPage' => $cursorToNextPage,
                'cursorToPreviousPage' => $cursorToPreviousPage,
                'total' => $total,
            ],
        ];
    }

    protected function getResultList(QueryBuilder $qb): array
    {
        if (!empty($this->cursor)) {
            $inspectedColumns = "";
            $comparisonValues = "";
            $parameters = [];
            foreach ($this->orders as $key => $order) {
                $inspectedColumns .= empty($inspectedColumns) ? "{$order['column']}" : ", {$order['column']}";
                $comparisonValues .= empty($comparisonValues) ? ":order{$key}" : ", :order{$key}";
                $parameters["order{$key}"] = $this->cursor['pointers'][$order['column']];
            }

            $firstOrderDirection = $this->orders[array_key_first($this->orders)]['direction'];
            $nextRecordsAreGreaterThanCursor =
                ($this->cursor['direction'] === 'NEXT' && $firstOrderDirection === 'ASC')
                || ($this->cursor['direction'] === 'PREVIOUS' && $firstOrderDirection === 'DESC');
            if ($nextRecordsAreGreaterThanCursor) {
                $qb->andWhere($qb->expr()->gt("($inspectedColumns)", "($comparisonValues)"));
            } else {
                $qb->andWhere($qb->expr()->lt("($inspectedColumns)", "($comparisonValues)"));
            }
            $qb->setParameters($parameters);
        }

        $qb->setMaxResults($this->pageSize + 1);
        foreach ($this->orders as $order) {
            $orderDirection = $order['direction'] == 'ASC' ? 'ASC' : 'DESC';
            $qb->addOrderBy($order['column'] ?? 'id', $orderDirection);
        }

        return $qb->executeQuery()->fetchAllAssociative();
    }

    protected function getTotalResult(QueryBuilder $totalResultQB): ?int
    {
        $totalResultQB
            ->select('COUNT(*) AS total')
            ->setMaxResults(1);
        return $totalResultQB->executeQuery()->fetchOne();
    }
}
