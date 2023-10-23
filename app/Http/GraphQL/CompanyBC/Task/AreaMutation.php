<?php

namespace App\Http\GraphQL\CompanyBC\Task;

use App\Http\Controllers\CompanyBC\InCompany\AreaStructure\AreaController;
use App\Http\GraphQL\AppContext;
use App\Http\GraphQL\GraphqlInputRequest;
use Company\Domain\Model\AreaStructure\Area;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Resources\Infrastructure\GraphQL\TypeRegistry;
use Resources\Infrastructure\Persistence\Doctrine\DoctrineGraphqlFieldsBuilder;

class AreaMutation extends ObjectType
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
                'type' => TypeRegistry::objectType(Area::class),
                'args' => [
                    'areaStructureId' => Type::nonNull(Type::id()),
                    ...DoctrineGraphqlFieldsBuilder::buildInputFields(Area::class)
                ],
                'resolve' => fn($root, $args, AppContext $app) => (new AreaController())
                        ->addChild($app->user, $app->getAggregateRootId('areaId'), new GraphqlInputRequest($args))
            ],
        ];
    }
}
