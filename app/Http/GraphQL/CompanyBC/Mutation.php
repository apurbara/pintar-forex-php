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
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(PersonnelController::class),
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(SalesActivityController::class),
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(AreaController::class),
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(ManagerController::class),
            ...ControllerToGraphqlFieldsMapper::mapMutationFields(SalesController::class),
        ];
    }
}
