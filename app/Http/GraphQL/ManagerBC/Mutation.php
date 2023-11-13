<?php

namespace App\Http\GraphQL\ManagerBC;

class Mutation extends ObjectType
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
            ...$this->closingRequest(),
        ];
    }

    //
    protected function registerTask(
            string $taskMetadata, AppContext $app, string $aggregateName, string $aggregateId): Type
    {
        $app->setAggregateRootId($aggregateName, $aggregateId);
        return TypeRegistry::objectType($taskMetadata);
    }

    //
    protected function closingRequest(): array
    {
        return [
            'closingRequest' => [
                'type' => TypeRegistry::type(PersonnelMutation::class),
                'args' => ['closingRequestId' => Type::nonNull(Type::id())],
                'resolve' => function ($root, $args, AppContext $app) {
                    $app->setAggregateRootId('closingRequestId', $args['closingRequestId']);
                    return TypeRegistry::type(PersonnelMutation::class);
                }
            ],
        ];
    }

}
