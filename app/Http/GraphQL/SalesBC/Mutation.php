<?php

namespace App\Http\GraphQL\SalesBC;

use App\Http\Controllers\SalesBC\AssignedCustomerController;
use App\Http\Controllers\SalesBC\ClosingRequestController;
use App\Http\Controllers\SalesBC\CustomerController;
use App\Http\Controllers\SalesBC\RecycleRequestController;
use App\Http\Controllers\SalesBC\SalesActivityReportController;
use App\Http\Controllers\SalesBC\SalesActivityScheduleController;
use App\Http\Controllers\SalesBC\SalesRoleInterface;
use App\Http\Controllers\SalesBC\VerificationReportController;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Resources\Infrastructure\GraphQL\ControllerToGraphqlFieldsMapper;
use Resources\Infrastructure\GraphQL\DoctrineEntityToGraphqlFieldMapper;
use Resources\Infrastructure\GraphQL\GraphqlInputRequest;
use Resources\Infrastructure\GraphQL\TypeRegistry;
use Sales\Domain\Model\AreaStructure\Area\Customer\VerificationReport;
use function app;

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
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(CustomerController::class),
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(AssignedCustomerController::class),
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(ClosingRequestController::class),
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(RecycleRequestController::class),
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(SalesActivityReportController::class),
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(SalesActivityScheduleController::class),
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(VerificationReportController::class),
            'submitCustomerVerificationReport' => [
                'type' => TypeRegistry::objectType(VerificationReport::class),
                'args' => [
                    ...DoctrineEntityToGraphqlFieldMapper::mapInputFields(VerificationReport::class),
                    'AssignedCustomer_id' => Type::id(),
                ],
                'resolve' => fn($root, $args) => app(VerificationReportController::class)
                        ->submitCustomerVerificationReport(app(SalesRoleInterface::class), $args['AssignedCustomer_id'],
                                new GraphqlInputRequest($args))
            ],
        ];
    }
}
