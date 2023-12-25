<?php

namespace App\Http\GraphQL\UserBC\Object;

use App\Http\Controllers\UserRole\UserRoleBuilder;
use GraphQL\Type\Definition\Type;
use Resources\Infrastructure\GraphQL\GraphqlObjectType;
use User\Domain\Model\Personnel;

class PersonnelLoginResponseGraph extends GraphqlObjectType
{

    protected function fieldDefinition(): array
    {
        return [
            ...parent::fieldDefinition(),
            'token' => [
                'type' => Type::string(),
                'resolve' => fn($root) => UserRoleBuilder::generateJwtToken(UserRoleBuilder::PERSONNEL, $root['id']),
            ],
//            'salesAssignments' => [
//                'type' => TypeRegistry::paginationType(Sales::class),
//                'args' => InputListSchema::paginationListSchema(),
//                'resolve' => function ($root, $args, AppContext $app) {
//                    return (new SalesAssignmentController())->viewList($app->user, new GraphqlInputRequest($args));
//                }
//            ],
//            'managerAssignments' => [
//                'type' => TypeRegistry::paginationType(Manager::class),
//                'args' => InputListSchema::paginationListSchema(),
//                'resolve' => function ($root, $args, AppContext $app) {
//                    return (new ManagerAssignmentController())->viewList($app->user, new GraphqlInputRequest($args));
//                }
//            ],
        ];
    }

    protected function getClassMetadata(): string
    {
        return Personnel::class;
    }
}
