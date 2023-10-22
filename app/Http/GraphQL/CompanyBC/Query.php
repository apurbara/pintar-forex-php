<?php

namespace App\Http\GraphQL\CompanyBC;

use App\Http\Controllers\CompanyBC\InCompany\PersonnelController;
use App\Http\GraphQL\AppContext;
use App\Http\GraphQL\GraphqlInputRequest;
use Company\Domain\Model\Personnel;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Resources\Infrastructure\GraphQL\InputListSchema;
use Resources\Infrastructure\GraphQL\Pagination;
use Resources\Infrastructure\GraphQL\TypeRegistry;

class Query extends ObjectType
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
            ...$this->personnelQuery(),
        ];
    }
    
    protected function personnelQuery(): array
    {
        return [
            'personnelList' => [
                'type' => new Pagination(TypeRegistry::objectType(Personnel::class)),
                'args' => InputListSchema::paginationListSchema(),
                'resolve' => fn($root, $args, AppContext $app) => (new PersonnelController())
                        ->viewList($app->user, new GraphqlInputRequest($args))
            ],
            'personnelDetail' => [
                'type' => TypeRegistry::objectType(Personnel::class),
                'args' => ['personnelId' => Type::nonNull(Type::id())],
                'resolve' => fn($root, $args, AppContext $app) => (new PersonnelController())
                        ->viewDetail($app->user, $args['personnelId'])
            ],
        ];
    }
    
}
