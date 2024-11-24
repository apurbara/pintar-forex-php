<?php

namespace Manager\Infrastructure\Persistence\Doctrine\Repository;

use Manager\Domain\DependencyModel\CustomerJourney;
use Manager\Domain\Task\Dependency\CustomerJourneyRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;

class DoctrineCustomerJourneyRepository extends DoctrineEntityRepository implements CustomerJourneyRepository
{

    public function anInitialCustomerJourney(): ?CustomerJourney
    {
        return $this->findOneBy([
                    'disabled' => false,
                    'initial' => true,
        ]);
    }
}
