<?php

namespace App\Http\GraphQL\UserBC;

use App\Http\GraphQL\UserBC\Task\GuestMutation;
use GraphQL\Type\Definition\ObjectType;
use Resources\Infrastructure\GraphQL\TypeRegistry;

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
            'byGuest' => [
                'type' => TypeRegistry::type(GuestMutation::class),
                'resolve' => fn() => TypeRegistry::type(GuestMutation::class),
            ]
        ];
    }
}
