<?php

namespace App\Http\GraphQL\CompanyBC;

use App\Http\Controllers\CompanyBC\InCompany\AreaController;
use App\Http\Controllers\CompanyBC\InCompany\AreaStructureController;
use App\Http\Controllers\CompanyBC\InCompany\ClosingRequestController;
use App\Http\Controllers\CompanyBC\InCompany\CommonSalesMetricController;
use App\Http\Controllers\CompanyBC\InCompany\CompanyMetricController;
use App\Http\Controllers\CompanyBC\InCompany\CustomerAssignmentController;
use App\Http\Controllers\CompanyBC\InCompany\CustomerController;
use App\Http\Controllers\CompanyBC\InCompany\CustomerJourneyController;
use App\Http\Controllers\CompanyBC\InCompany\CustomerVerificationController;
use App\Http\Controllers\CompanyBC\InCompany\ManagerController;
use App\Http\Controllers\CompanyBC\InCompany\PersonnelController;
use App\Http\Controllers\CompanyBC\InCompany\RecycleRequestController;
use App\Http\Controllers\CompanyBC\InCompany\SalesActivityController;
use App\Http\Controllers\CompanyBC\InCompany\SalesController;
use App\Http\Controllers\CompanyBC\InCompany\SalesRankController;
use GraphQL\Type\Definition\ObjectType;
use Resources\Infrastructure\GraphQL\ControllerToGraphqlFieldsMapper;

class Query extends ObjectType
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
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(AreaStructureController::class),
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(CustomerJourneyController::class),
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(CustomerVerificationController::class),
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(PersonnelController::class),
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(SalesActivityController::class),
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(AreaController::class),
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(ManagerController::class),
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(SalesController::class),
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(CustomerController::class),
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(CustomerAssignmentController::class),
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(RecycleRequestController::class),
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(ClosingRequestController::class),
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(CompanyMetricController::class),
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(SalesRankController::class),
            ...ControllerToGraphqlFieldsMapper::mapQueryFields(CommonSalesMetricController::class),
        ];
    }
}
