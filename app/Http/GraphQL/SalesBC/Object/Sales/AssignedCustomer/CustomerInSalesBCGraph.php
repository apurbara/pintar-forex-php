<?php

namespace App\Http\GraphQL\SalesBC\Object\Sales\AssignedCustomer;

use App\Http\Controllers\CompanyBC\InCompany\AreaStructure\AreaController;
use App\Http\Controllers\SalesBC\VerificationReportController;
use App\Http\GraphQL\AppContext;
use App\Http\GraphQL\CompanyBC\Object\AreaStructure\AreaGraph;
use App\Http\GraphQL\GraphqlInputRequest;
use App\Http\GraphQL\SalesBC\Object\Sales\AssignedCustomer\Customer\VerificationReportInSalesBCGraph;
use Resources\Infrastructure\GraphQL\GraphqlObjectType;
use Resources\Infrastructure\GraphQL\InputListSchema;
use Resources\Infrastructure\GraphQL\Pagination;
use Resources\Infrastructure\GraphQL\TypeRegistry;
use Sales\Domain\Model\AreaStructure\Area\Customer;

class CustomerInSalesBCGraph extends GraphqlObjectType
{

    protected function fieldDefinition(): array
    {
        return [
            ...parent::fieldDefinition(),
            'area' => [
                'type' => TypeRegistry::objectType(AreaGraph::class),
                'resolve' => fn($root, $args, AppContext $app) => (new AreaController())
                        ->viewDetail($app->user, $root['Area_id'])
            ],
            'verificationReports' => [
                'type' => new Pagination(TypeRegistry::objectType(VerificationReportInSalesBCGraph::class)),
                'args' => InputListSchema::paginationListSchema(),
                'resolve' => fn($root, $args, AppContext $app) => (new VerificationReportController())
                        ->viewList($app->user, new GraphqlInputRequest($args))
            ],
        ];
    }

    protected function getClassMetadata(): string
    {
        return Customer::class;
    }
}
