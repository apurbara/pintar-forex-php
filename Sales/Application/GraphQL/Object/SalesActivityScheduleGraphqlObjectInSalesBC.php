<?php

namespace Sales\Application\GraphQL\Object;

use Doctrine\ORM\EntityManager;
use GraphQL\Type\Definition\Type;
use Resources\Infrastructure\GraphQL\GraphqlObjectType;
use Resources\Infrastructure\GraphQL\TypeRegistry;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule;
use Sales\Domain\Model\Sales\FactFindingAssignment;
use Sales\Domain\Model\Sales\GreetingAssignment;
use Sales\Domain\Model\Sales\StrikingAssignment;
use function app;

class SalesActivityScheduleGraphqlObjectInSalesBC extends GraphqlObjectType
{
    
    protected function getClassMetadata(): string
    {
        return SalesActivitySchedule::class;
    }
    
    protected function fieldDefinition(): array
    {
        return [
            ...parent::fieldDefinition(),
            'GreetingAssignment_id' => Type::id(),
            'greetingAssignment' => [
                'type' => TypeRegistry::objectType(GreetingAssignment::class),
                'resolve' => fn($root) => app(EntityManager::class)->getRepository(GreetingAssignment::class)->queryOneById($root['GreetingAssignment_id']),
            ],
            'FactFindingAssignment_id' => Type::id(),
            'factFindingAssignment' => [
                'type' => TypeRegistry::objectType(FactFindingAssignment::class),
                'resolve' => fn($root) => app(EntityManager::class)->getRepository(FactFindingAssignment::class)->queryOneById($root['FactFindingAssignment_id']),
            ],
            'StrikingAssignment_id' => Type::id(),
            'strikingAssignment' => [
                'type' => TypeRegistry::objectType(StrikingAssignment::class),
                'resolve' => fn($root) => app(EntityManager::class)->getRepository(StrikingAssignment::class)->queryOneById($root['StrikingAssignment_id']),
            ],
        ];
    }
}
