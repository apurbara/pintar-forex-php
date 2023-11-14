<?php

namespace Sales\Infrastructure\Persistence\Doctrine\Repository;

use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Sales\Domain\Model\CustomerJourney;
use Sales\Domain\Task\CustomerJourney\CustomerJourneyRepository;

class DoctrineCustomerJourneyRepository extends DoctrineEntityRepository implements CustomerJourneyRepository
{
    
    public function anInitialCustomerJourney(): ?CustomerJourney
    {
        return $this->findOneBy([
            'initial' => true,
        ]);
    }

    public function ofId(string $id): CustomerJourney
    {
        return $this->findOneByIdOrDie($id);
    }
}
