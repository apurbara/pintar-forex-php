<?php

namespace App\Http\GraphQL\CompanyBC\Object\AreaStructure;

use App\Http\Controllers\CompanyBC\InCompany\AreaStructure\AreaController;
use App\Http\GraphQL\AppContext;
use App\Http\GraphQL\CompanyBC\Object\AreaStructureGraph;
use App\Http\GraphQL\GraphqlInputRequest;
use Company\Domain\Model\AreaStructure;
use Company\Domain\Model\AreaStructure\Area;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineAreaStructureRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Resources\Infrastructure\GraphQL\GraphqlObjectType;
use Resources\Infrastructure\GraphQL\InputListSchema;
use Resources\Infrastructure\GraphQL\TypeRegistry;
use function app;

class AreaGraph extends GraphqlObjectType
{

    protected function fieldDefinition(): array
    {
        return [
            ...parent::fieldDefinition(),
            'areaStructure' => [
                'type' => TypeRegistry::objectType(AreaStructureGraph::class),
                'resolve' => fn ($root) => (new DoctrineAreaStructureRepository(app(EntityManager::class), new ClassMetadata(AreaStructure::class)))->fetchOneById($root['AreaStructure_id'])
            ],
            'parent' => [
                'type' => TypeRegistry::objectType(AreaGraph::class),
                'resolve' => function ($root, $args, AppContext $app) {
                    if ($root['Area_idOfParent']) {
                        return (new AreaController())->viewDetail($app->user, $root['Area_idOfParent']);
                    }
                }
            ],
            'children' => [
                'type' => TypeRegistry::paginationType(AreaGraph::class),
//                'type' => new Pagination(TypeRegistry::objectType(AreaGraph::class)),
                'args' => InputListSchema::paginationListSchema(),
                'resolve' => function ($root, $args, AppContext $app) {
                    $args['filters'][] = ['column' => 'Area.Area_idOfParent', 'value' => $root['id']];
                    return (new AreaController())->viewList($app->user, new GraphqlInputRequest($args));
                }
            ],
        ];
    }

    protected function getClassMetadata(): string
    {
        return Area::class;
    }
}
