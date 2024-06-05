<?php

namespace App\Http\GraphQL\SalesBC;

use App\Http\Controllers\SalesBC\BySales\ClosingRequestController;
use App\Http\Controllers\SalesBC\BySales\CustomerAssignmentController;
use App\Http\Controllers\SalesBC\BySales\CustomerController;
use App\Http\Controllers\SalesBC\BySales\RecycleRequestController;
use App\Http\Controllers\SalesBC\BySales\SalesActivityReportController;
use App\Http\Controllers\SalesBC\BySales\SalesActivityScheduleController;
use App\Http\Controllers\SalesBC\BySales\VerificationReportController;
use App\Http\Controllers\SalesBC\SalesRole;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Resources\Infrastructure\GraphQL\ControllerToGraphqlFieldsMapper;
use Resources\Infrastructure\GraphQL\DoctrineEntityToGraphqlFieldMapper;
use Resources\Infrastructure\GraphQL\GraphqlInputRequest;
use Resources\Infrastructure\GraphQL\TypeRegistry;
use Sales\Domain\DependencyModel\AreaStructure\Area\Customer\VerificationReport;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule;
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
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(CustomerAssignmentController::class),
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(ClosingRequestController::class),
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(RecycleRequestController::class),
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(SalesActivityReportController::class),
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(SalesActivityScheduleController::class),
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(VerificationReportController::class),
            'submitCustomerVerificationReport' => [
                'type' => TypeRegistry::objectType(VerificationReport::class),
                'args' => [
                    ...DoctrineEntityToGraphqlFieldMapper::mapInputFields(VerificationReport::class),
                    'CustomerAssignment_id' => Type::id(),
                ],
                'resolve' => fn($root, $args) => app(VerificationReportController::class)
                        ->submitCustomerVerificationReport(app(SalesRole::class), $args['CustomerAssignment_id'],
                                new GraphqlInputRequest($args))
            ],
            'submitInitialSalesActivityReport' => [
                'type' => TypeRegistry::objectType(SalesActivitySchedule::class),
                'args' => [
                    'content' => Type::string(),
                    'CustomerAssignment_id' => Type::id(),
                ],
                'resolve' => fn($root, $args) => app(SalesActivityReportController::class)
                        ->submitInitialSalesActivityReport(app(SalesRole::class), new GraphqlInputRequest($args))
            ],
        ];
    }
}
