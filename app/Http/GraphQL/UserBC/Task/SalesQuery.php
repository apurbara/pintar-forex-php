<?php

namespace App\Http\GraphQL\UserBC\Task;

use App\Http\Controllers\UserBC\BySales\AccountController;
use GraphQL\Type\Definition\ObjectType;
use Resources\Infrastructure\GraphQL\ControllerToGraphqlFieldsMapper;

class SalesQuery extends ObjectType
{

    public function __construct()
    {
        parent::__construct([
            'fields' => fn() => $this->fieldDefinitions(),
        ]);
    }

    //
    protected function fieldDefinitions(): array
    {
        return [
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(AccountController::class),
        ];
    }
}
