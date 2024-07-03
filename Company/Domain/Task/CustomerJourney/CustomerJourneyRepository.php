<?php

namespace Company\Domain\Task\CustomerJourney;

use Company\Domain\Model\CustomerJourney;

interface CustomerJourneyRepository
{

    public function nextIdentity(): string;

    public function add(CustomerJourney $customerJourney): void;

    public function anInitialCustomerJourney(): ?CustomerJourney;

    public function ofId(string $id): CustomerJourney;

    //
    public function aCustomerJourneyDetail(string $id): array;

    public function customerJourneyList(array $paginationSchema): array;

    public function allActiveCustomerJourney(array $searchSchema): array;
}
