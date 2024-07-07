<?php

namespace Company\Domain\Service;

use Company\Domain\Task\CustomerAssignment\CustomerAssignmentDistributionServiceInterface;

class CustomerAssignmentDistributionServiceBuilder
{

    const LOAD_BALANCE_DISTRIBUTION = 'LOAD_BALANCE_DISTRIBUTION';
    const EVEN_DISTRIBUTION = 'EVEN_DISTRIBUTION';

    public static function build(?string $strategy = null): CustomerAssignmentDistributionServiceInterface
    {
        return match ($strategy) {
            static::LOAD_BALANCE_DISTRIBUTION => new LoadBalanceCustomerAssignmentDistributionService(),
            default => new EvenlyCustomerAssignmentDistributionService(),
        };
    }
}
