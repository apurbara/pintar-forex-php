<?php

namespace App\Http\GraphQL\SalesBC\Task;

use App\Http\Controllers\SalesBC\SalesActivityScheduleController;
use App\Http\GraphQL\AppContext;
use App\Http\GraphQL\GraphqlInputRequest;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Resources\Infrastructure\GraphQL\TypeRegistry;
use Resources\Infrastructure\Persistence\Doctrine\DoctrineGraphqlFieldsBuilder;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivitySchedule;

class AssignedCustomerMutation extends ObjectType
{

    public function __construct()
    {
        parent::__construct([
            'fields' => fn() => $this->fieldDefinitions(),
        ]);
    }

    protected function fieldDefinitions(): array
    {
        return [
            'submitSalesActivitySchedule' => [
                'type' => TypeRegistry::objectType(SalesActivitySchedule::class),
                'args' => [
                    'salesActivityId' => Type::nonNull(Type::id()),
                    ...DoctrineGraphqlFieldsBuilder::buildInputFields(SalesActivitySchedule::class),
                ],
                'resolve' => fn($root, $args, AppContext $app) => (new SalesActivityScheduleController())
                        ->submitSchedule($app->user, $app->getAggregateRootId('assignedCustomerId'),
                                new GraphqlInputRequest($args))
            ],
        ];
    }
}
