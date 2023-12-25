<?php

namespace App\Http\GraphQL\ManagerBC;

use App\Http\Controllers\ManagerBC\AssignedCustomerController;
use App\Http\Controllers\ManagerBC\ClosingRequestController;
use App\Http\Controllers\ManagerBC\RecycleRequestController;
use GraphQL\Type\Definition\ObjectType;
use Resources\Infrastructure\GraphQL\ControllerToGraphqlFieldsMapper;

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
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(AssignedCustomerController::class),
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(ClosingRequestController::class),
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(RecycleRequestController::class),
        ];
    }
}
