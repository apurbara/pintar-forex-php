<?php

namespace App\Http\GraphQL\SalesBC\Task;

use App\Http\Controllers\SalesBC\SalesActivityReportController;
use App\Http\GraphQL\AppContext;
use App\Http\GraphQL\GraphqlInputRequest;
use GraphQL\Type\Definition\ObjectType;
use Resources\Infrastructure\GraphQL\TypeRegistry;
use Resources\Infrastructure\Persistence\Doctrine\DoctrineGraphqlFieldsBuilder;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivitySchedule\SalesActivityReport;

class SalesActivityScheduleMutation extends ObjectType
{

    public function __construct()
    {
        parent::__construct([
            'fields' => fn() => $this->fieldDefinitions(),
        ]);
    }

    protected function fieldDefinitions(): array
    {
        return [
            'submitReport' => [
                'type' => TypeRegistry::objectType(SalesActivityReport::class),
                'args' => DoctrineGraphqlFieldsBuilder::buildInputFields(SalesActivityReport::class),
                'resolve' => fn($root, $args, AppContext $app) => (new SalesActivityReportController())
                        ->submitReport($app->user, $app->getAggregateRootId('salesActivityScheduleId'),
                                new GraphqlInputRequest($args))
            ],
        ];
    }
}
