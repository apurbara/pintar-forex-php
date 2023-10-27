<?php

namespace App\Http\GraphQL\UserBC;

use GraphQL\Type\Definition\ObjectType;

class Query extends ObjectType
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
        ];
    }
}
