<?php

namespace App\Http\GraphQL\CompanyBC;

use App\Http\Controllers\CompanyBC\CompanyUserRoleInterface;
use App\Http\Controllers\CompanyBC\InCompany\AreaController;
use App\Http\Controllers\CompanyBC\InCompany\AreaStructureController;
use App\Http\Controllers\CompanyBC\InCompany\ClosingRequestController;
use App\Http\Controllers\CompanyBC\InCompany\CommonSalesMetricController;
use App\Http\Controllers\CompanyBC\InCompany\CompanyMetricController;
use App\Http\Controllers\CompanyBC\InCompany\CustomerAssignmentController;
use App\Http\Controllers\CompanyBC\InCompany\CustomerJourneyController;
use App\Http\Controllers\CompanyBC\InCompany\CustomerVerificationController;
use App\Http\Controllers\CompanyBC\InCompany\ManagerController;
use App\Http\Controllers\CompanyBC\InCompany\RecycleRequestController;
use App\Http\Controllers\CompanyBC\InCompany\SalesActivityController;
use App\Http\Controllers\CompanyBC\InCompany\SalesController;
use App\Http\Controllers\CompanyBC\InCompany\SalesPerformanceMetricController;
use App\Http\Controllers\CompanyBC\InCompany\SalesRankController;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Resources\Infrastructure\GraphQL\ControllerToGraphqlFieldsMapper;
use Resources\Infrastructure\GraphQL\CustomTypes\NoResponse;
use Resources\Infrastructure\GraphQL\GraphqlInputRequest;
use Resources\Infrastructure\GraphQL\TypeRegistry;
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
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(AreaStructureController::class),
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(CustomerJourneyController::class),
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(CustomerVerificationController::class),
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(SalesActivityController::class),
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(AreaController::class),
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(ManagerController::class),
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(SalesController::class),
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(ClosingRequestController::class),
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(RecycleRequestController::class),
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(CompanyMetricController::class),
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(SalesRankController::class),
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(CommonSalesMetricController::class),
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(SalesPerformanceMetricController::class),
            ...$this->customerAssignmentMutation(),
        ];
    }
    
    protected function customerAssignmentMutation(): array
    {
        return [
            'assignMultipleCustomerToMultipleSales' => [
                'type' => TypeRegistry::type(NoResponse::class),
                'args' => [
                    'salesList' => Type::listOf(Type::id()),
                    'customerList' => Type::listOf(Type::id()),
                    'distributionStrategy' => Type::string(),
                    'initiateSchedules' => Type::boolean(),
                ],
                'resolve' => fn($root, $args) => app(CustomerAssignmentController::class)
                        ->assignedMultipleCustomerToMultipleSales(app(CompanyUserRoleInterface::class),
                                new GraphqlInputRequest($args))
            ],
        ];
    }
}
