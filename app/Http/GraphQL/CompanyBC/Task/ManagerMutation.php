<?php

namespace App\Http\GraphQL\CompanyBC\Task;

use App\Http\Controllers\CompanyBC\InCompany\Personnel\Manager\SalesController;
use App\Http\GraphQL\AppContext;
use App\Http\GraphQL\CompanyBC\Object\Personnel\Manager\SalesGraph;
use App\Http\GraphQL\GraphqlInputRequest;
use Company\Domain\Model\Personnel\Manager\Sales;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Resources\Infrastructure\GraphQL\TypeRegistry;
use Resources\Infrastructure\Persistence\Doctrine\DoctrineGraphqlFieldsBuilder;

class ManagerMutation extends ObjectType
{

    public function __construct()
    {
        parent::__construct([
            'fields' => fn() => $this->fieldsDefinition(),
        ]);
    }

    protected function fieldsDefinition(): array
    {
        return [
            'assignSales' => [
                'type' => TypeRegistry::objectType(SalesGraph::class),
                'args' => [
                    'personnelId' => Type::nonNull(Type::id()),
                    'areaId' => Type::nonNull(Type::id()),
                    ...DoctrineGraphqlFieldsBuilder::buildInputFields(Sales::class),
                ],
                'resolve' => fn($root, $args, AppContext $app) => (new SalesController())
                        ->assign($app->user, $app->getAggregateRootId('managerId'), new GraphqlInputRequest($args))
            ],
        ];
    }
}
