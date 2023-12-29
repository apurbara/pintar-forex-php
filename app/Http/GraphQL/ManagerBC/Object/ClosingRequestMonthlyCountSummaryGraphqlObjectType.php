<?php

namespace App\Http\GraphQL\ManagerBC\Object;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class ClosingRequestMonthlyCountSummaryGraphqlObjectType extends ObjectType
{
    public function __construct()
    {
        parent::__construct([
            'fields' => fn() => [
                'closingCount' => Type::int(),
                'yearMonth' => Type::string(),
            ],
        ]);
    }
}
