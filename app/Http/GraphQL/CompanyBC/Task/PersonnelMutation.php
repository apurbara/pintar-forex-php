<?php

namespace App\Http\GraphQL\CompanyBC\Task;

use App\Http\Controllers\CompanyBC\InCompany\Personnel\ManagerController;
use App\Http\GraphQL\AppContext;
use App\Http\GraphQL\CompanyBC\Object\Personnel\ManagerGraph;
use Company\Domain\Model\Personnel\Manager;
use GraphQL\Type\Definition\ObjectType;
use Resources\Infrastructure\GraphQL\TypeRegistry;
use Resources\Infrastructure\Persistence\Doctrine\DoctrineGraphqlFieldsBuilder;

class PersonnelMutation extends ObjectType
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
            'assignManager' => [
                'type' => TypeRegistry::objectType(ManagerGraph::class),
                'args' => DoctrineGraphqlFieldsBuilder::buildInputFields(Manager::class),
                'resolve' => fn($root, $args, AppContext $app) => (new ManagerController())
                        ->assign($app->user, $app->getAggregateRootId('personnelId'))
            ],
        ];
    }
}
