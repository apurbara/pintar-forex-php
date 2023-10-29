<?php

namespace App\Http\GraphQL\UserBC\Object;

use App\Http\Controllers\UserRole\PersonnelRole;
use App\Http\Controllers\UserRole\UserRoleBuilder;
use App\Http\GraphQL\AppContext;
use App\Http\GraphQL\SalesBC\Object\SalesInSalesBCGraph;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use GraphQL\Type\Definition\Type;
use Resources\Infrastructure\GraphQL\GraphqlObjectType;
use Resources\Infrastructure\GraphQL\TypeRegistry;
use User\Domain\Model\Personnel;
use User\Domain\Model\Personnel\Sales;
use User\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesRepository;
use function app;

class PersonnelLoginResponseGraph extends GraphqlObjectType
{

    protected function fieldDefinition(): array
    {
        return [
            ...parent::fieldDefinition(),
            'token' => [
                'type' => Type::string(),
                'resolve' => fn($root) => UserRoleBuilder::generateJwtToken(UserRoleBuilder::ADMIN, $root['id']),
            ],
            'salesAssignments' => [
                'type' => TypeRegistry::objectType(SalesInSalesBCGraph::class),
                'resolve' => function ($root, $args, AppContext $app) {
                    $em = app(EntityManager::class);
                    $sales = (new DoctrineSalesRepository($em, new ClassMetadata(Sales::class)))
                            ->salesAssignmentListBelongsToPersonnel($root['id']);
                    if (!empty($sales)) {
                        $app->user = (new PersonnelRole($root['id']))->authorizedAsSales($sales['id']);
                    }
                    return $sales;
                }
            ],
        ];
    }

    protected function getClassMetadata(): string
    {
        return Personnel::class;
    }
}
