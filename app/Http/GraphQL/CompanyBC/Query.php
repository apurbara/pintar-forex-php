<?php

namespace App\Http\GraphQL\CompanyBC;

use App\Http\Controllers\CompanyBC\InCompany\AreaStructure\AreaController;
use App\Http\Controllers\CompanyBC\InCompany\AreaStructureController;
use App\Http\Controllers\CompanyBC\InCompany\CustomerJourneyController;
use App\Http\Controllers\CompanyBC\InCompany\CustomerVerificationController;
use App\Http\Controllers\CompanyBC\InCompany\Personnel\Manager\SalesController;
use App\Http\Controllers\CompanyBC\InCompany\Personnel\ManagerController;
use App\Http\Controllers\CompanyBC\InCompany\PersonnelController;
use App\Http\Controllers\CompanyBC\InCompany\SalesActivityController;
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
        ];
    }
}
