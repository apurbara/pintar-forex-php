<?php

namespace Resources\Infrastructure\GraphQL;

interface GraphqlQueryableRepository
{

    public function queryOneById(string $id): ?array;

    public function queryOneBy(array $filters): ?array;

    public function queryAllList(array $searchSchema): array;

    public function queryPaginationList(array $paginationSchema): array;
}
