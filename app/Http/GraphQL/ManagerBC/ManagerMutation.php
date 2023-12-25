<?php

namespace App\Http\GraphQL\ManagerBC;

use App\Http\Controllers\ManagerBC\ClosingRequestController;
use App\Http\Controllers\ManagerBC\RecycleRequestController;
use App\Http\GraphQL\ManagerBC\Object\Manager\Sales\AssignedCustomer\ClosingRequestInManagerBCGraph;
use App\Http\GraphQL\ManagerBC\Object\Manager\Sales\AssignedCustomer\RecycleRequestInManagerBCGraph;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Resources\Infrastructure\GraphQL\AppContext;
use Resources\Infrastructure\GraphQL\TypeRegistry;

class ManagerMutation extends ObjectType
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
            ...$this->closingRequestMutation(),
            ...$this->recycleRequestMutation(),
        ];
    }

    //
    protected function closingRequestMutation(): array
    {
        return [
            'acceptClosingRequest' => [
                'type' => TypeRegistry::type(ClosingRequestInManagerBCGraph::class),
                'args' => ['id' => Type::nonNull(Type::id())],
                'resolve' => fn($root, $args, AppContext $app) => (new ClosingRequestController())
                        ->accept($app->user, $args['id']),
            ],
            'rejectClosingRequest' => [
                'type' => TypeRegistry::type(ClosingRequestInManagerBCGraph::class),
                'args' => ['id' => Type::nonNull(Type::id())],
                'resolve' => fn($root, $args, AppContext $app) => (new ClosingRequestController())
                        ->reject($app->user, $args['id']),
            ],
        ];
    }
    //
    protected function recycleRequestMutation(): array
    {
        return [
            'approveRecycleRequest' => [
                'type' => TypeRegistry::type(RecycleRequestInManagerBCGraph::class),
                'args' => ['id' => Type::nonNull(Type::id())],
                'resolve' => fn($root, $args, AppContext $app) => (new RecycleRequestController())
                        ->approve($app->user, $args['id']),
            ],
            'rejectRecycleRequest' => [
                'type' => TypeRegistry::type(RecycleRequestInManagerBCGraph::class),
                'args' => ['id' => Type::nonNull(Type::id())],
                'resolve' => fn($root, $args, AppContext $app) => (new RecycleRequestController())
                        ->reject($app->user, $args['id']),
            ],
        ];
    }
}
