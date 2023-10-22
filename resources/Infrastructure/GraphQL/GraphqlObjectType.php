<?php

namespace Resources\Infrastructure\GraphQL;

use GraphQL\Type\Definition\ObjectType;
use Resources\Infrastructure\Persistence\Doctrine\DoctrineGraphqlFieldsBuilder;

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
}
