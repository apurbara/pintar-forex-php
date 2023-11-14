<?php

namespace Sales\Domain\Task\CustomerJourney;

use Sales\Domain\Model\CustomerJourney;

interface CustomerJourneyRepository
{

    public function ofId(string $id): CustomerJourney;

    public function anInitialCustomerJourney(): ?CustomerJourney;
}
