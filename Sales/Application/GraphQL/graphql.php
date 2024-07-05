<?php

namespace Sales\Application\GraphQL;

// Test this using following command
// php -S localhost:8080 ./graphql.php
//require_once __DIR__ . '/../../vendor/autoload.php';


use GraphQL\Type\Schema;
use Resources\Infrastructure\GraphQL\TypeRegistry;
use Sales\Domain\DependencyModel\Customer;
use function base_path;

TypeRegistry::registerPredefinedClassMaps([
    'CustomerInput' => Customer::class,
]);
$schema = new Schema([
    'query' => TypeRegistry::type(Query::class),
    'mutation' => TypeRegistry::type(Mutation::class),
    'typeLoader' => static fn($name) => TypeRegistry::type($name),
]);

return require base_path() . '/resources/Infrastructure/GraphQL/executeGraphqlQuery.php';
