<?php

namespace App\Http\GraphQL\UserBC\Task;

use App\Http\Controllers\UserBC\ByPersonnel\AccountController;
use GraphQL\Type\Definition\ObjectType;
use Resources\Infrastructure\GraphQL\ControllerToGraphqlFieldsMapper;

class PersonnelQuery extends ObjectType
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
