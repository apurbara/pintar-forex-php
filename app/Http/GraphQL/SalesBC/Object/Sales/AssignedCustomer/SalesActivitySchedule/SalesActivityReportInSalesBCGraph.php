<?php

namespace App\Http\GraphQL\SalesBC\Object\Sales\AssignedCustomer\SalesActivitySchedule;

use Resources\Infrastructure\GraphQL\GraphqlObjectType;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivitySchedule\SalesActivityReport;

class SalesActivityReportInSalesBCGraph extends GraphqlObjectType
{

  protected function getClassMetadata(): string
  {
    return SalesActivityReport::class;
  }
}
