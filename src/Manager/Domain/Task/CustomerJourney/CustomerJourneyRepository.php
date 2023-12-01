<?php

namespace Manager\Domain\Task\CustomerJourney;

use Manager\Domain\Model\CustomerJourney;

interface CustomerJourneyRepository
{
    public function anInitialCustomerJourney(): ?CustomerJourney;
}
