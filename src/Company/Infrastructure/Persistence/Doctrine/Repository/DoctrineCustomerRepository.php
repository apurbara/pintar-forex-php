<?php

namespace Company\Infrastructure\Persistence\Doctrine\Repository;

use Company\Domain\Model\AreaStructure\Area\Customer;
use Company\Domain\Task\InCompany\Customer\CustomerRepository;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineAllListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrinePaginationListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;
use SharedContext\Domain\Enum\CustomerAssignmentStatus;

class DoctrineCustomerRepository extends DoctrineEntityRepository implements CustomerRepository
{

    public function add(Customer $customer): void
    {
        $this->persist($customer);
    }

    public function isPhoneAvailable(string $phone): bool
    {
        $filters = [
            new Filter($phone, 'Customer.phone'),
        ];
        return empty($this->fetchOneBy($filters));
    }

    //
    private function applyFilter(QueryBuilder $qb, &$searchSchema): void
    {
        foreach ($searchSchema['filters'] as $key => $filter) {
            if (($filter['column'] ?? null) === 'AssignedCustomer.status') {
                $customerAssignmentQB = $this->getEntityManager()->getConnection()->createQueryBuilder();
                $customerAssignmentQB->select('1')
                        ->from('AssignedCustomer')
                        ->andWhere($customerAssignmentQB->expr()->eq('AssignedCustomer.Customer_id', 'Customer.id'))
                        ->andWhere($customerAssignmentQB->expr()->in('AssignedCustomer.status', ':status'));

                $qb->andWhere("EXISTS ({$customerAssignmentQB->getSQL()})")
                        ->setParameter('status', $filter['value'], ArrayParameterType::STRING);
                unset($searchSchema['filters'][$key]);
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
                unset($searchSchema['filters'][$key]);
            }
            if (($filter['column'] ?? null) === 'hasAssignment') {
                $activeCustomerAssignmentQB = $this->getEntityManager()->getConnection()->createQueryBuilder();
                $activeCustomerAssignmentQB->select('1')
                        ->from('AssignedCustomer')
                        ->andWhere($activeCustomerAssignmentQB->expr()->eq('AssignedCustomer.Customer_id', 'Customer.id'));
                if ($filter['value'] == true) {
                    $qb->andWhere("EXISTS ({$activeCustomerAssignmentQB->getSQL()})");
                } else {
                    $qb->andWhere("NOT EXISTS ({$activeCustomerAssignmentQB->getSQL()})");
                }
                unset($searchSchema['filters'][$key]);
            }
        }
    }
    
    public function customerList(array $paginationSchema): array
    {
        $verificationScoreSubQuery = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $verificationScoreSubQuery->addSelect('VerificationReport.Customer_id')
                ->addSelect('SUM(CustomerVerification.weight) verificationScore')
                ->from('VerificationReport')
                ->innerJoin('VerificationReport', 'CustomerVerification', 'CustomerVerification', 'VerificationReport.CustomerVerification_id = CustomerVerification.id')
                ->groupBy('VerificationReport.Customer_id');
        $qb = $this->createCoreQueryBuilder()
                ->addSelect('verificationScoreSubQuery.verificationScore')
                ->leftJoin('Customer', sprintf('(%s)', $verificationScoreSubQuery->getSQL()), 'verificationScoreSubQuery', 'verificationScoreSubQuery.Customer_id = Customer.id');
        
        $this->applyFilter($qb, $paginationSchema);
        
        return DoctrinePaginationListCategory::fromSchema($paginationSchema)
                        ->paginateResult($qb, $this->getTableName());
    }

    public function allCustomer(array $searchSchema): array
    {
        
        $verificationScoreSubQuery = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $verificationScoreSubQuery->addSelect('VerificationReport.Customer_id')
                ->addSelect('SUM(CustomerVerification.weight) verificationScore')
                ->from('VerificationReport')
                ->innerJoin('VerificationReport', 'CustomerVerification', 'CustomerVerification', 'VerificationReport.CustomerVerification_id = CustomerVerification.id')
                ->groupBy('VerificationReport.Customer_id');
        $qb = $this->dbalQueryBuilder();
        $qb->addSelect('Customer.name')
                ->addSelect('Customer.phone')
                ->addSelect('Customer.email')
                ->addSelect('Customer.source')
                ->addSelect('verificationScoreSubQuery.verificationScore')
                ->from('Customer')
                ->leftJoin('Customer', sprintf('(%s)', $verificationScoreSubQuery->getSQL()), 'verificationScoreSubQuery', 'verificationScoreSubQuery.Customer_id = Customer.id');
        return DoctrineAllListCategory::fromSchema($searchSchema)
                        ->fetchResult($qb);
    }

    public function aCustomer(string $id): array
    {
        return $this->queryOneById($id);
    }
}
