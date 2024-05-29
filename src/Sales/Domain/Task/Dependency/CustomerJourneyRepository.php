<?php

namespace Sales\Domain\Task\Dependency;

use Sales\Domain\DependencyModel\CustomerJourney;

interface CustomerJourneyRepository
{

    public function ofId(string $id): CustomerJourney;

    public function anInitialCustomerJourney(): ?CustomerJourney;
}
