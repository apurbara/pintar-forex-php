<?php

namespace App\Http\GraphQL\SalesBC\Object\Sales\AssignedCustomer\SalesActivitySchedule;

use App\Http\GraphQL\SalesBC\Object\Sales\AssignedCustomer\SalesActivityScheduleInSalesBCGraph;
use Doctrine\ORM\EntityManager;
use Resources\Infrastructure\GraphQL\GraphqlObjectType;
use Resources\Infrastructure\GraphQL\TypeRegistry;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivitySchedule;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivitySchedule\SalesActivityReport;

class SalesActivityReportInSalesBCGraph extends GraphqlObjectType
{
  protected function fieldDefinition(): array
  {
    return [
      ...parent::fieldDefinition(),
      'salesActivitySchedule' => [
        'type' => TypeRegistry::objectType(SalesActivityScheduleInSalesBCGraph::class),
        'resolve' => fn ($root) => app(EntityManager::class)->getRepository(SalesActivitySchedule::class)
          ->fetchOneById($root['SalesActivitySchedule_id'])
      ],
    ];
  }

  protected function getClassMetadata(): string
  {
    return SalesActivityReport::class;
  }
}
