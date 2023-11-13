<?php

namespace Company\Infrastructure\Persistence\Doctrine\Repository;

use Company\Domain\Model\CustomerJourney;
use Company\Domain\Task\InCompany\CustomerJourney\CustomerJourneyRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrinePaginationListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;

class DoctrineCustomerJourneyRepository extends DoctrineEntityRepository implements CustomerJourneyRepository
{

    public function aCustomerJourneyDetail(string $id): array
    {
        return $this->fetchOneByIdOrDie($id);
    }

    public function add(CustomerJourney $customerJourney): void
    {
        $this->persist($customerJourney);
    }

    public function anInitialCustomerJourney(): ?CustomerJourney
    {
        return $this->findOneBy([
                    'disabled' => false,
                    'initial' => true,
        ]);
    }

    public function customerJourneyList(array $paginationSchema): array
    {
        $doctrinePaginationListCategory = DoctrinePaginationListCategory::fromSchema($paginationSchema);
        return $this->fetchPaginationList($doctrinePaginationListCategory);
    }
    
    public function fetchInitialCustomerJourneyDetail(): array
    {
        $filters = [
            new Filter(true, 'CustomerJourney.initial'),
            new Filter(false, 'CustomerJourney.disabled'),
        ];
        return $this->fetchOneBy($filters);
    }
}
