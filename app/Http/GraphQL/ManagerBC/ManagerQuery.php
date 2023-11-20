<?php

namespace App\Http\GraphQL\ManagerBC;

use App\Http\Controllers\ManagerBC\ClosingRequestController;
use App\Http\Controllers\ManagerBC\RecycleRequestController;
use App\Http\GraphQL\AppContext;
use App\Http\GraphQL\GraphqlInputRequest;
use App\Http\GraphQL\ManagerBC\Object\Manager\Sales\AssignedCustomer\ClosingRequestInManagerBCGraph;
use App\Http\GraphQL\ManagerBC\Object\Manager\Sales\AssignedCustomer\RecycleRequestInManagerBCGraph;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Resources\Infrastructure\GraphQL\InputListSchema;
use Resources\Infrastructure\GraphQL\Pagination;
use Resources\Infrastructure\GraphQL\TypeRegistry;

class ManagerQuery extends ObjectType
{

    public function __construct()
    {
        parent::__construct([
            'fields' => fn() => $this->fieldDefinition(),
        ]);
    }

    protected function fieldDefinition(): array
    {
        return [
            ...$this->closingRequestQuery(),
            ...$this->recycleRequestQuery(),
        ];
    }
    //
    protected function closingRequestQuery(): array
    {
        return [
            'closingRequestList' => [
                'type' => new Pagination(TypeRegistry::objectType(ClosingRequestInManagerBCGraph::class)),
                'args' => InputListSchema::paginationListSchema(),
                'resolve' => fn($root, $args, AppContext $app) => (new ClosingRequestController())
                        ->viewList($app->user, new GraphqlInputRequest($args))
            ],
            'closingRequestDetail' => [
                'type' => TypeRegistry::objectType(ClosingRequestInManagerBCGraph::class),
                'args' => ['id' => Type::nonNull(Type::id())],
                'resolve' => fn($root, $args, AppContext $app) => (new ClosingRequestController())
                        ->viewDetail($app->user, $args['id'])
            ],
        ];
    }
    //
    protected function recycleRequestQuery(): array
    {
        return [
            'recycleRequestList' => [
                'type' => new Pagination(TypeRegistry::objectType(RecycleRequestInManagerBCGraph::class)),
                'args' => InputListSchema::paginationListSchema(),
                'resolve' => fn($root, $args, AppContext $app) => (new RecycleRequestController())
                        ->viewList($app->user, new GraphqlInputRequest($args))
            ],
            'recycleRequestDetail' => [
                'type' => TypeRegistry::objectType(RecycleRequestInManagerBCGraph::class),
                'args' => ['id' => Type::nonNull(Type::id())],
                'resolve' => fn($root, $args, AppContext $app) => (new RecycleRequestController())
                        ->viewDetail($app->user, $args['id'])
            ],
        ];
    }
}
