<?php

namespace App\Http\GraphQL;

use App\Http\Controllers\UserRole\UserRoleBuilder;
use function request;

class AppContext
{

    public readonly object $user;
    protected array $aggregateRoot = [];

    public function __construct()
    {
        $this->user = UserRoleBuilder::generateUserRole(request());
    }

    public function setAggregateRootId(string $aggregateName, string $aggregateId): void
    {
        $this->aggregateRoot[$aggregateName] = $aggregateId;
    }

    public function getAggregateRootId(string $aggregateName)
    {
        return $this->aggregateRoot[$aggregateName] ?? null;
    }
    
}
