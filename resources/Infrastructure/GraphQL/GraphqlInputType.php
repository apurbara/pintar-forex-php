<?php

namespace Resources\Infrastructure\GraphQL;

use GraphQL\Type\Definition\InputObjectType;
use Resources\Infrastructure\Persistence\Doctrine\DoctrineGraphqlFieldsBuilder;

abstract class GraphqlInputType extends InputObjectType
{

    abstract protected function getClassMetadata(): string;

    public function __construct()
    {
        parent::__construct([
            'fields' => fn() => $this->fieldDefinition(),
        ]);
    }

    protected function fieldDefinition(): array
    {
        return DoctrineGraphqlFieldsBuilder::buildInputFields($this->getClassMetadata());
    }
}
