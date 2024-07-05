<?php

namespace Sales\Infrastructure\Persistence\Doctrine\Repository;

use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Sales\Domain\DependencyModel\CustomerJourney;
use Sales\Domain\Task\Dependency\CustomerJourneyRepository;

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
