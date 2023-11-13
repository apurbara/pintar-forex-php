<?php

namespace App\Http\GraphQL\ManagerBC\Task;

use GraphQL\Type\Definition\ObjectType;

class ClosingRequestMutation extends ObjectType
{
    public function __construct()
    {
        parent::__construct([
            'fields' => fn() => $this->fieldDefinition()
        ]);
    }
    
    protected function fieldDefinition(): array
    {
        return [
            'accept' => [
                'type' => TypeRegistry::objectType(AreaGraph::class),
                'args' => [
                    'areaStructureId' => Type::nonNull(Type::id()),
                    ...DoctrineGraphqlFieldsBuilder::buildInputFields(Area::class)
                ],
                'resolve' => fn($root, $args, AppContext $app) => (new AreaController())
                        ->addChild($app->user, $app->getAggregateRootId('areaId'), new GraphqlInputRequest($args))
            ],
        ];
    }
}
