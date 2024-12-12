<?php

namespace Sales\Application\GraphQL;

use GraphQL\Type\Definition\ObjectType;
use Resources\Infrastructure\GraphQL\ControllerToGraphqlFieldsMapper;
use Sales\Application\Controllers\CustomerAssignment\CustomerController;

class MutationAggregatedInCustomerAssignment extends ObjectType
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
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(CustomerController::class),
        ];
    }
}
