<?php

namespace Sales\Domain\Task\Dependency;

use Sales\Domain\DependencyModel\CustomerVerification;

interface CustomerVerificationRepository
{

    public function ofId(string $id): CustomerVerification;

    /**
     * 
     * @return CustomerVerification[]
     */
    public function allActiveCustomerVerification();
}
