<?php

namespace Resources\Infrastructure\GraphQL;

use Doctrine\ORM\EntityManager;
use GraphQL\Type\Definition\ObjectType;
use Resources\Infrastructure\Persistence\Doctrine\DoctrineGraphqlFieldsBuilder;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use function app;

abstract class GraphqlObjectType extends ObjectType
{

    abstract protected  function getClassMetadata(): string;
    
    public function __construct()
    {
        parent::__construct([
            'fields' => fn() => $this->fieldDefinition(),
        ]);
    }

    protected function fieldDefinition(): array
    {
        return DoctrineGraphqlFieldsBuilder::buildObjectFields($this->getClassMetadata());
    }
    
    protected function buildDoctrineRepository(string $classMetadata): DoctrineEntityRepository
    {
        return app(EntityManager::class)->getRepository($classMetadata);
    }
    
}
