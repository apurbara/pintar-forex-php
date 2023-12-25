<?php

namespace App\Http\GraphQL\CompanyBC\Task;

use App\Http\Controllers\CompanyBC\InCompany\AreaStructure\AreaController;
use App\Http\Controllers\CompanyBC\InCompany\AreaStructureController;
use App\Http\GraphQL\CompanyBC\Object\AreaStructure\AreaGraph;
use App\Http\GraphQL\CompanyBC\Object\AreaStructureGraph;
use Company\Domain\Model\AreaStructure;
use Company\Domain\Model\AreaStructure\Area;
use GraphQL\Type\Definition\ObjectType;
use Resources\Infrastructure\GraphQL\AppContext;
use Resources\Infrastructure\GraphQL\GraphqlInputRequest;
use Resources\Infrastructure\GraphQL\TypeRegistry;
use Resources\Infrastructure\Persistence\Doctrine\DoctrineGraphqlFieldsBuilder;

class AreaStructureMutation extends ObjectType
{

    public function __construct()
    {
        parent::__construct([
            'fields' => fn() => $this->fieldsDefinition(),
        ]);
    }

    protected function fieldsDefinition(): array
    {
        return [
            'addChild' => [
                'type' => TypeRegistry::objectType(AreaStructureGraph::class),
                'args' => DoctrineGraphqlFieldsBuilder::buildInputFields(AreaStructure::class),
                'resolve' => fn($root, $args, AppContext $app) => (new AreaStructureController())
                        ->addChild($app->user, $app->getAggregateRootId('areaStructureId'),
                                new GraphqlInputRequest($args))
            ],
            'addRootArea' => [
                'type' => TypeRegistry::objectType(AreaGraph::class),
                'args' => DoctrineGraphqlFieldsBuilder::buildInputFields(Area::class),
                'resolve' => fn($root, $args, AppContext $app) => (new AreaController())
                        ->addRoot($app->user, $app->getAggregateRootId('areaStructureId'),
                                new GraphqlInputRequest($args))
            ],
        ];
    }
}
