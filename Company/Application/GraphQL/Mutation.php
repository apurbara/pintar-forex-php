<?php

namespace Company\Application\GraphQL;

use Company\Application\Controllers\CityController;
use Company\Application\Controllers\CommonSalesMetricController;
use Company\Application\Controllers\CompanyMetricController;
use Company\Application\Controllers\CustomerJourneyController;
use Company\Application\Controllers\CustomerVerificationController;
use Company\Application\Controllers\ManagerController;
use Company\Application\Controllers\ProvinceController;
use Company\Application\Controllers\SalesActivityController;
use Company\Application\Controllers\SalesController;
use Company\Application\Controllers\SalesPerformanceMetricController;
use Company\Application\Controllers\SalesRankController;
use GraphQL\Type\Definition\ObjectType;
use Resources\Infrastructure\GraphQL\ControllerToGraphqlFieldsMapper;

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
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(CustomerJourneyController::class),
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(CustomerVerificationController::class),
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(SalesActivityController::class),
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(ManagerController::class),
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(SalesController::class),
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(CompanyMetricController::class),
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(SalesRankController::class),
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(CommonSalesMetricController::class),
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(SalesPerformanceMetricController::class),
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(ProvinceController::class),
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(CityController::class),
        ];
    }
}
