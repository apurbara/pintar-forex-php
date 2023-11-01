<?php

namespace App\Http\GraphQL\SalesBC\Task;

use App\Http\Controllers\SalesBC\ClosingRequestController;
use App\Http\Controllers\SalesBC\RecycleRequestController;
use App\Http\Controllers\SalesBC\SalesActivityScheduleController;
use App\Http\Controllers\SalesBC\VerificationReportController;
use App\Http\GraphQL\AppContext;
use App\Http\GraphQL\GraphqlInputRequest;
use App\Http\GraphQL\SalesBC\Object\Sales\AssignedCustomer\ClosingRequestInSalesBCGraph;
use App\Http\GraphQL\SalesBC\Object\Sales\AssignedCustomer\Customer\VerificationReportInSalesBCGraph;
use App\Http\GraphQL\SalesBC\Object\Sales\AssignedCustomer\RecycleRequestInSalesBCGraph;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Resources\Infrastructure\GraphQL\TypeRegistry;
use Resources\Infrastructure\Persistence\Doctrine\DoctrineGraphqlFieldsBuilder;
use Sales\Domain\Model\AreaStructure\Area\Customer\VerificationReport;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\ClosingRequest;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\RecycleRequest;
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
            'submitCustomerVerificationReport' => [
                'type' => TypeRegistry::objectType(VerificationReportInSalesBCGraph::class),
                'args' => [
                    'customerVerificationId' => Type::nonNull(Type::id()),
                    ...DoctrineGraphqlFieldsBuilder::buildInputFields(VerificationReport::class),
                ],
                'resolve' => fn($root, $args, AppContext $app) => (new VerificationReportController())
                        ->submit($app->user, $app->getAggregateRootId('assignedCustomerId'),
                                new GraphqlInputRequest($args))
            ],
            'submitClosingRequest' => [
                'type' => TypeRegistry::objectType(ClosingRequestInSalesBCGraph::class),
                'args' => DoctrineGraphqlFieldsBuilder::buildInputFields(ClosingRequest::class),
                'resolve' => fn($root, $args, AppContext $app) => (new ClosingRequestController())
                        ->submit($app->user, $app->getAggregateRootId('assignedCustomerId'),
                                new GraphqlInputRequest($args))
            ],
            'submitRecycleRequest' => [
                'type' => TypeRegistry::objectType(RecycleRequestInSalesBCGraph::class),
                'args' => DoctrineGraphqlFieldsBuilder::buildInputFields(RecycleRequest::class),
                'resolve' => fn($root, $args, AppContext $app) => (new RecycleRequestController())
                        ->submit($app->user, $app->getAggregateRootId('assignedCustomerId'),
                                new GraphqlInputRequest($args))
            ],
        ];
    }
}
