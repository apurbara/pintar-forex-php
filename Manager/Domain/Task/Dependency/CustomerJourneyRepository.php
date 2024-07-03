<?php

namespace Manager\Domain\Task\Dependency;

use Manager\Domain\DependencyModel\CustomerJourney;

interface CustomerJourneyRepository
{

    public function anInitialCustomerJourney(): CustomerJourney;
}
