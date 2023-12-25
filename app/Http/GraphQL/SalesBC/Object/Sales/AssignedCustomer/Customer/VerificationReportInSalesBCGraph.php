<?php

namespace App\Http\GraphQL\SalesBC\Object\Sales\AssignedCustomer\Customer;

use App\Http\Controllers\CompanyBC\InCompany\CustomerVerificationController;
use App\Http\GraphQL\CompanyBC\Object\CustomerVerificationGraph;
use Resources\Infrastructure\GraphQL\AppContext;
use Resources\Infrastructure\GraphQL\GraphqlObjectType;
use Resources\Infrastructure\GraphQL\TypeRegistry;
use Sales\Domain\Model\AreaStructure\Area\Customer\VerificationReport;

class VerificationReportInSalesBCGraph extends GraphqlObjectType
{

    protected function fieldDefinition(): array
    {
        return [
            ...parent::fieldDefinition(),
            'customerVerification' => [
                'type' => TypeRegistry::objectType(CustomerVerificationGraph::class),
                'resolve' => fn($root, $args, AppContext $app) => (new CustomerVerificationController())
                        ->viewDetail($app->user, $root['CustomerVerification_id'])
            ],
        ];
    }

    protected function getClassMetadata(): string
    {
        return VerificationReport::class;
    }
}
