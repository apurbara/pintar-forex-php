<?php

namespace App\Http\GraphQL\SalesBC;

// Test this using following command
// php -S localhost:8080 ./graphql.php
//require_once __DIR__ . '/../../vendor/autoload.php';


use GraphQL\Type\Schema;
use Resources\Infrastructure\GraphQL\TypeRegistry;

$schema = new Schema([
    'query' => TypeRegistry::type(SalesQuery::class),
    'mutation' => TypeRegistry::type(SalesMutation::class),
    'typeLoader' => static fn($name) => TypeRegistry::load($name),
]);

return require __DIR__ . '/../executeGraphqlQuery.php';
