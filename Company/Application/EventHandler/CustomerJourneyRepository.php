<?php

namespace Company\Application\EventHandler;

use Company\Domain\Model\CustomerJourney;

interface CustomerJourneyRepository
{

    public function anInitialCustomerJourney(): ?CustomerJourney;
}
