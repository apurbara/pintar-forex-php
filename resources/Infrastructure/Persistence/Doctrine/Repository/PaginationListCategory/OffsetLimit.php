<?php

namespace Resources\Infrastructure\Persistence\Doctrine\Repository\PaginationListCategory;

use Doctrine\DBAL\Query\QueryBuilder;
use Resources\Infrastructure\Persistence\Doctrine\Repository\PageLimitInterface;

class OffsetLimit implements PageLimitInterface
{

    protected int $page;
    protected int $pageSize;
    protected array $orders;

    public function __construct(int $page = 1, int $pageSize = 20,
            array $orders = [['column' => 'id', 'direction' => 'ASC']])
    {
        $this->page = $page;
        $this->pageSize = ($pageSize > 100) ? 100 : $pageSize;
        $this->orders = $orders;
    }

    public static function fromSchema(array $schema = []): static
    {
        return new static(
                $schema['page'] ?? 1, $schema['pageSize'] ?? 20,
                $schema['orders'] ?? [['column' => 'id', 'direction' => 'ASC']]);
    }

    public function paginateResult(QueryBuilder $qb): array
    {
        $qb->setFirstResult($this->pageSize * ($this->page - 1));
        $qb->setMaxResults($this->pageSize);

        foreach ($this->orders as $order) {
            $qb->addOrderBy($order['column'] ?? 'id', $order['direction'] ?? 'ASC');
        }

        $results = $qb->executeQuery()->fetchAllAssociative();

        $qb->select('COUNT(*) AS total')
                ->setFirstResult(0)
                ->setMaxResults(1);

        return [
            'list' => $results,
            'offsetLimit' => [
                'page' => $this->page,
                'pageSize' => $this->pageSize,
                'total' => $qb->executeQuery()->fetchOne(),
            ],
        ];
    }
}
