<?php

namespace Company\Infrastructure\Persistence\Doctrine\Repository;

use Company\Domain\Task\InCompany\Customer\CustomerRepository;
use Doctrine\DBAL\ArrayParameterType;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrinePaginationListCategory;
use SharedContext\Domain\Enum\CustomerAssignmentStatus;

class DoctrineCustomerRepository extends DoctrineEntityRepository implements CustomerRepository
{

    public function customerList(array $paginationSchema): array
    {
        $qb = $this->createCoreQueryBuilder();
        foreach ($paginationSchema['filters'] as $key => $filter) {
            if (($filter['column'] ?? null) === 'AssignedCustomer.status') {
                $customerAssignmentQB = $this->getEntityManager()->getConnection()->createQueryBuilder();
                $customerAssignmentQB->select('1')
                        ->from('AssignedCustomer')
                        ->andWhere($customerAssignmentQB->expr()->eq('AssignedCustomer.Customer_id', 'Customer.id'))
                        ->andWhere($customerAssignmentQB->expr()->in('AssignedCustomer.status', ':status'));

                $qb->andWhere("EXISTS ({$customerAssignmentQB->getSQL()})")
                        ->setParameter('status', $filter['value'], ArrayParameterType::STRING);
                unset($paginationSchema['filters'][$key]);
            }
            if (($filter['column'] ?? null) === 'hasActiveAssignment') {
                $activeCustomerAssignmentQB = $this->getEntityManager()->getConnection()->createQueryBuilder();
                $activeCustomerAssignmentQB->select('1')
                        ->from('AssignedCustomer')
                        ->andWhere($activeCustomerAssignmentQB->expr()->eq('AssignedCustomer.Customer_id', 'Customer.id'))
                        ->andWhere($activeCustomerAssignmentQB->expr()->eq('AssignedCustomer.status',
                                        "'" . CustomerAssignmentStatus::ACTIVE->value . "'"));
                if ($filter['value'] == true) {
                    $qb->andWhere("EXISTS ({$activeCustomerAssignmentQB->getSQL()})");
                } else {
                    $qb->andWhere("NOT EXISTS ({$activeCustomerAssignmentQB->getSQL()})");
                }
                unset($paginationSchema['filters'][$key]);
            }
        }
        return DoctrinePaginationListCategory::fromSchema($paginationSchema)
                        ->paginateResult($qb, $this->getTableName());
    }
}
