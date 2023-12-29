<?php

namespace App\Http\GraphQL\ManagerBC\Object;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class ClosingRequestMonthlyTotalTransactionSummaryGraphqlObjectType extends ObjectType
{
    public function __construct()
    {
        parent::__construct([
            'fields' => fn() => [
                'totalTransaction' => Type::int(),
                'yearMonth' => Type::string(),
            ],
        ]);
    }
}
