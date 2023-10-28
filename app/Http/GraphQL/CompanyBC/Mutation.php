<?php

namespace App\Http\GraphQL\CompanyBC;

use App\Http\Controllers\CompanyBC\InCompany\AreaStructureController;
use App\Http\Controllers\CompanyBC\InCompany\CustomerVerificationController;
use App\Http\Controllers\CompanyBC\InCompany\PersonnelController;
use App\Http\Controllers\CompanyBC\InCompany\SalesActivityController;
use App\Http\GraphQL\AppContext;
use App\Http\GraphQL\CompanyBC\Object\AreaStructureGraph;
use App\Http\GraphQL\CompanyBC\Object\CustomerVerificationGraph;
use App\Http\GraphQL\CompanyBC\Object\PersonnelGraph;
use App\Http\GraphQL\CompanyBC\Object\SalesActivityGraph;
use App\Http\GraphQL\CompanyBC\Task\AreaMutation;
use App\Http\GraphQL\CompanyBC\Task\AreaStructureMutation;
use App\Http\GraphQL\CompanyBC\Task\ManagerMutation;
use App\Http\GraphQL\CompanyBC\Task\PersonnelMutation;
use App\Http\GraphQL\GraphqlInputRequest;
use Company\Domain\Model\AreaStructure;
use Company\Domain\Model\CustomerVerification;
use Company\Domain\Model\Personnel;
use Company\Domain\Model\SalesActivity;
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
            ...$this->managerMutation(),
            ...$this->customerVerificationMutation(),
            ...$this->salesActivityMutation(),
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
                'type' => TypeRegistry::objectType(PersonnelGraph::class),
                'args' => DoctrineGraphqlFieldsBuilder::buildInputFields(Personnel::class),
                'resolve' => fn($root, $args, AppContext $app) => (new PersonnelController())
                        ->add($app->user, new GraphqlInputRequest($args))
            ],
            'personnel' => [
                'type' => TypeRegistry::type(PersonnelMutation::class),
                'args' => ['personnelId' => Type::nonNull(Type::id())],
                'resolve' => function ($root, $args, AppContext $app) {
                    $app->setAggregateRootId('personnelId', $args['personnelId']);
                    return TypeRegistry::type(PersonnelMutation::class);
                }
            ],
        ];
    }

    protected function areaStructureMutation(): array
    {
        return [
            'addRootAreaStructure' => [
                'type' => TypeRegistry::objectType(AreaStructureGraph::class),
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

    protected function managerMutation(): array
    {
        return [
            'manager' => [
                'type' => TypeRegistry::type(ManagerMutation::class),
                'args' => ['managerId' => Type::nonNull(Type::id())],
                'resolve' => function ($root, $args, AppContext $app) {
                    $app->setAggregateRootId('managerId', $args['managerId']);
                    return TypeRegistry::type(ManagerMutation::class);
                }
            ],
        ];
    }

    protected function customerVerificationMutation(): array
    {
        return [
            'addCustomerVerification' => [
                'type' => TypeRegistry::objectType(CustomerVerificationGraph::class),
                'args' => DoctrineGraphqlFieldsBuilder::buildInputFields(CustomerVerification::class),
                'resolve' => fn($root, $args, AppContext $app) => (new CustomerVerificationController())
                        ->add($app->user, new GraphqlInputRequest($args))
            ],
        ];
    }

    protected function salesActivityMutation(): array
    {
        return [
            'setInitialSalesActivity' => [
                'type' => TypeRegistry::objectType(SalesActivityGraph::class),
                'args' => DoctrineGraphqlFieldsBuilder::buildInputFields(SalesActivity::class),
                'resolve' => fn($root, $args, AppContext $app) => (new SalesActivityController())
                        ->setInitial($app->user, new GraphqlInputRequest($args))
            ],
            'addSalesActivity' => [
                'type' => TypeRegistry::objectType(SalesActivityGraph::class),
                'args' => DoctrineGraphqlFieldsBuilder::buildInputFields(SalesActivity::class),
                'resolve' => fn($root, $args, AppContext $app) => (new SalesActivityController())
                        ->add($app->user, new GraphqlInputRequest($args))
            ],
        ];
    }

}
