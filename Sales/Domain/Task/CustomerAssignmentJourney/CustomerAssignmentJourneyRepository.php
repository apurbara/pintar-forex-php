<?php

namespace Sales\Domain\Task\CustomerAssignmentJourney;

interface CustomerAssignmentJourneyRepository
{
    public function customerAssignmentJourneyListBelongsToSales(string $salesId, array $paginationSchema): array;
}
