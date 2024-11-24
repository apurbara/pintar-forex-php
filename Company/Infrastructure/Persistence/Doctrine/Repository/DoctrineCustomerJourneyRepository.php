<?php

namespace Company\Infrastructure\Persistence\Doctrine\Repository;

use Company\Application\EventHandler\CustomerJourneyRepository as CustomerJourneyRepository2;
use Company\Domain\Model\CustomerJourney;
use Company\Domain\Task\CustomerJourney\CustomerJourneyRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineAllListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrinePaginationListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;

class DoctrineCustomerJourneyRepository extends DoctrineEntityRepository implements CustomerJourneyRepository, CustomerJourneyRepository2
{

    public function ofId(string $id): CustomerJourney
    {
        return $this->findOneByIdOrDie($id);
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

    //
    public function aCustomerJourneyDetail(string $id): array
    {
        return $this->fetchOneByIdOrDie($id);
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

    public function allActiveCustomerJourney(array $searchSchema): array
    {
        $doctrineAllListCategory = DoctrineAllListCategory::fromSchema($searchSchema)
                ->addFilter(new Filter(false, 'CustomerJourney.disabled'));
        return $this->fetchAllList($doctrineAllListCategory);
    }
}
