<?php

namespace Resources\Infrastructure\GraphQL;

use GraphQL\Type\Definition\ObjectType;

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
        return DoctrineEntityToGraphqlFieldMapper::mapObjectFields($this->getClassMetadata());
    }
    
//    protected function buildDoctrineRepository(string $classMetadata): DoctrineEntityRepository
//    {
//        return app(EntityManager::class)->getRepository($classMetadata);
//    }
    
}
