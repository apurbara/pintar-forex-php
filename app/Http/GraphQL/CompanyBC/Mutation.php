<?php

namespace App\Http\GraphQL\CompanyBC;

use App\Http\Controllers\CompanyBC\InCompany\AreaStructureController;
use App\Http\Controllers\CompanyBC\InCompany\PersonnelController;
use App\Http\GraphQL\AppContext;
use App\Http\GraphQL\CompanyBC\Task\AreaMutation;
use App\Http\GraphQL\CompanyBC\Task\AreaStructureMutation;
use App\Http\GraphQL\GraphqlInputRequest;
use Company\Domain\Model\AreaStructure;
use Company\Domain\Model\Personnel;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Resources\Infrastructure\GraphQL\TypeRegistry;
use Resources\Infrastructure\Persistence\Doctrine\DoctrineGraphqlFieldsBuilder;

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
            ...$this->personnelMutation(),
            ...$this->areaStructureMutation(),
            ...$this->areaMutation(),
        ];
    }

    //
    protected function registerTask(
            string $taskMetadata, AppContext $app, string $aggregateName, string $aggregateId): Type
    {
        $app->setAggregateRootId($aggregateName, $aggregateId);
        return TypeRegistry::objectType($taskMetadata);
    }

    //
    protected function personnelMutation(): array
    {
        return [
            'addPersonnel' => [
                'type' => TypeRegistry::objectType(Personnel::class),
                'args' => DoctrineGraphqlFieldsBuilder::buildInputFields(Personnel::class),
                'resolve' => fn($root, $args, AppContext $app) => (new PersonnelController())
                        ->add($app->user, new GraphqlInputRequest($args))
            ],
        ];
    }

    protected function areaStructureMutation(): array
    {
        return [
            'addRootAreaStructure' => [
                'type' => TypeRegistry::objectType(AreaStructure::class),
                'args' => DoctrineGraphqlFieldsBuilder::buildInputFields(AreaStructure::class),
                'resolve' => fn($root, $args, AppContext $app) => (new AreaStructureController())
                        ->addRoot($app->user, new GraphqlInputRequest($args))
            ],
            'areaStructure' => [
                'type' => TypeRegistry::type(AreaStructureMutation::class),
                'args' => ['areaStructureId' => Type::nonNull(Type::id())],
                'resolve' => function ($root, $args, AppContext $app) {
                    $app->setAggregateRootId('areaStructureId', $args['areaStructureId']);
                    return TypeRegistry::type(AreaStructureMutation::class);
                }
            ],
        ];
    }

    protected function areaMutation(): array
    {
        return [
            'area' => [
                'type' => TypeRegistry::type(AreaMutation::class),
                'args' => ['areaId' => Type::nonNull(Type::id())],
                'resolve' => function ($root, $args, AppContext $app) {
                    $app->setAggregateRootId('areaId', $args['areaId']);
                    return TypeRegistry::type(AreaMutation::class);
                }
            ],
        ];
    }
}
