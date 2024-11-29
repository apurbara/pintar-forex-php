<?php

namespace Sales\Infrastructure\Persistence\Doctrine\Repository;

use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;
use Sales\Domain\Task\Dependency\CustomerRepository;

class DoctrineCustomerRepository extends DoctrineEntityRepository implements CustomerRepository
{

    public function isEmailAvailable(string $email): bool
    {
        $filters = [new Filter($email, 'Customer.email')];
        return empty($this->fetchOneBy($filters));
    }

    public function isPhoneAvailable(string $phone): bool
    {
        $filters = [new Filter($phone, 'Customer.phone')];
        return empty($this->fetchOneBy($filters));
    }

    //
    public function aCustomerAssociateWithAssignment(string $customerAssignmentId)
    {
        $qb = $this->createCoreQueryBuilder();
        $qb->leftJoin('Customer', 'GreetingAssignment', 'GreetingAssignment',
                        'GreetingAssignment.Customer_id = Customer.id')
                ->leftJoin('Customer', 'FactFindingAssignment', 'FactFindingAssignment',
                        'FactFindingAssignment.Customer_id = Customer.id')
                ->leftJoin('Customer', 'StrikingAssignment', 'StrikingAssignment',
                        'StrikingAssignment.Customer_id = Customer.id')
                ->andWhere($qb->expr()->or(
                                $qb->expr()->eq('GreetingAssignment.id', ':customerAssignmentId'),
                                $qb->expr()->eq('FactFindingAssignment.id', ':customerAssignmentId'),
                                $qb->expr()->eq('StrikingAssignment.id', ':customerAssignmentId')
                        ))
                ->setParameter('customerAssignmentId', $customerAssignmentId)
                ->setMaxResults(1);
        return $qb->executeQuery()->fetchAssociative() ?: null;
    }
}
