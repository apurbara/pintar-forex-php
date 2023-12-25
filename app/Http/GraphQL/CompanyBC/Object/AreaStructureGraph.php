<?php

namespace App\Http\GraphQL\CompanyBC\Object;

use App\Http\Controllers\CompanyBC\InCompany\AreaStructureController;
use Company\Domain\Model\AreaStructure;
use Resources\Infrastructure\GraphQL\AppContext;
use Resources\Infrastructure\GraphQL\GraphqlInputRequest;
use Resources\Infrastructure\GraphQL\GraphqlObjectType;
use Resources\Infrastructure\GraphQL\InputListSchema;
use Resources\Infrastructure\GraphQL\TypeRegistry;

class AreaStructureGraph extends GraphqlObjectType
{

    protected function fieldDefinition(): array
    {
        return [
            ...parent::fieldDefinition(),
            'parent' => [
                'type' => TypeRegistry::objectType(AreaStructureGraph::class),
                'resolve' => function ($root, $args, AppContext $app) {
                    if ($root['AreaStructure_idOfParent']) {
                        return (new AreaStructureController())->viewDetail($app->user, $root['AreaStructure_idOfParent']);
                    }
                },
            ],
            'children' => [
                'type' => TypeRegistry::paginationType(AreaStructureGraph::class),
//                'type' => new Pagination(TypeRegistry::objectType(AreaStructureGraph::class)),
                'args' => InputListSchema::paginationListSchema(),
                'resolve' => function ($root, $args, AppContext $app) {
                    $args['filters'][] = ['column' => 'AreaStructure.AreaStructure_idOfParent', 'value' => $root['id']];
                    return (new AreaStructureController())->viewList($app->user, new GraphqlInputRequest($args));
                }
            ],
        ];
    }

    protected function getClassMetadata(): string
    {
        return AreaStructure::class;
    }
}
