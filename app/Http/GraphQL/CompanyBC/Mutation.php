<?php

namespace App\Http\GraphQL\CompanyBC;

use App\Http\Controllers\CompanyBC\InCompany\PersonnelController;
use App\Http\GraphQL\AppContext;
use App\Http\GraphQL\GraphqlInputRequest;
use Company\Domain\Model\Personnel;
use GraphQL\Type\Definition\ObjectType;
use Resources\Infrastructure\GraphQL\TypeRegistry;
use Resources\Infrastructure\Persistence\Doctrine\DoctrineGraphqlFieldsBuilder;
use SebastianBergmann\Type\Type;

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

}
