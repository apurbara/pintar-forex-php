<?php

namespace Sales\Application\GraphQL;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Resources\Infrastructure\GraphQL\ControllerToGraphqlFieldsMapper;
use Resources\Infrastructure\GraphQL\CustomTypes\NoResponse;
use Resources\Infrastructure\GraphQL\DoctrineEntityToGraphqlFieldMapper;
use Resources\Infrastructure\GraphQL\GraphqlInputRequest;
use Resources\Infrastructure\GraphQL\TypeRegistry;
use Sales\Application\Controllers\AccountController;
use Sales\Application\Controllers\ClosingRequestController;
use Sales\Application\Controllers\CustomerAssignmentController;
use Sales\Application\Controllers\CustomerController;
use Sales\Application\Controllers\LoginController;
use Sales\Application\Controllers\RecycleRequestController;
use Sales\Application\Controllers\SalesActivityReportController;
use Sales\Application\Controllers\SalesActivityScheduleController;
use Sales\Application\Controllers\VerificationReportController;
use Sales\Application\GraphQL\Object\SalesLoginResponse;
use Sales\Domain\DependencyModel\Customer\VerificationReport;
use Sales\Domain\Model\Sales;
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
            ...$this->login(),
            ...$this->accountMutation(),
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(AccountController::class),
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
                        ->submitCustomerVerificationReport(app(Sales::class), $args['CustomerAssignment_id'],
                                new GraphqlInputRequest($args))
            ],
            'submitInitialSalesActivityReport' => [
                'type' => TypeRegistry::objectType(SalesActivitySchedule::class),
                'args' => [
                    'content' => Type::string(),
                    'CustomerAssignment_id' => Type::id(),
                ],
                'resolve' => fn($root, $args) => app(SalesActivityReportController::class)
                        ->submitInitialSalesActivityReport(app(Sales::class), new GraphqlInputRequest($args))
            ],
        ];
    }
    
    protected function login(): array
    {
        return [
            'login' => [
                'type' => TypeRegistry::objectType(SalesLoginResponse::class),
                'args' => [
                    'email' => Type::string(),
                    'password' => Type::string(),
                ],
                'resolve' => fn($root, $args) => app(LoginController::class)->login(new GraphqlInputRequest($args))
            ],
        ];
    }

    protected function accountMutation(): array
    {
        return [
            'changePassword' => [
                'type' => TypeRegistry::type(NoResponse::class),
                'args' => [
                    'previousPassword' => Type::string(),
                    'newPassword' => Type::string(),
                ],
                'resolve' => fn($root, $args) => app(AccountController::class)
                        ->changePassword(app(Sales::class), new GraphqlInputRequest($args))
            ],
        ];
    }
}
