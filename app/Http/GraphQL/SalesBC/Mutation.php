<?php

namespace App\Http\GraphQL\SalesBC;

use App\Http\Controllers\SalesBC\AssignedCustomerController;
use App\Http\GraphQL\AppContext;
use App\Http\GraphQL\GraphqlInputRequest;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Resources\Infrastructure\GraphQL\TypeRegistry;
use Resources\Infrastructure\Persistence\Doctrine\DoctrineGraphqlFieldsBuilder;
use Sales\Domain\Model\AreaStructure\Area\Customer;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;

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
        $type = new ObjectType([
            'name' => 'salesMutation', 
            'fields' => fn() => [
                ...$this->assignedCustomerMutation(),
            ],
        ]);
        return [
            'sales' => [
                'type' => $type,
                'args' => ['salesId' => Type::nonNull(Type::id())],
                'resolve' => function($root, $args, AppContext $app) use($type) {
                    $app->user = $app->user->authorizedAsSales($args['salesId']);
                    return $type;
                }
            ]
        ];
    }

    protected function assignedCustomerMutation(): array
    {
        return [
            'registerNewCustomer' => [
                'type' => TypeRegistry::objectType(AssignedCustomer::class),
                'args' => [
                    'areaId' => Type::nonNull(Type::id()),
                    ...DoctrineGraphqlFieldsBuilder::buildInputFields(Customer::class),
                ],
                'resolve' => fn($root, $args, AppContext $app) => (new AssignedCustomerController())
                        ->registerNewCustomer($app->user, new GraphqlInputRequest($args))
            ],
        ];
    }
}
