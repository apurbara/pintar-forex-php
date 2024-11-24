<?php

namespace Company\Infrastructure\Persistence\Doctrine\Repository;

use Company\Domain\Model\Customer;
use Company\Domain\Task\Customer\CustomerRepository;
use Doctrine\DBAL\Query\QueryBuilder;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineAllListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrinePaginationListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;
use Shared\Domain\Enum\CustomerAssignmentStatus;

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
            if (($filter['column'] ?? null) === 'hasActiveGreetingAssignment') {
                $activeGreetingAssignmentQB = $this->getEntityManager()->getConnection()->createQueryBuilder();
                $activeGreetingAssignmentQB->select('1')
                        ->from('GreetingAssignment')
                        ->andWhere($activeGreetingAssignmentQB->expr()->eq('GreetingAssignment.Customer_id', 'Customer.id'))
                        ->andWhere($activeGreetingAssignmentQB->expr()->eq('GreetingAssignment.status', "'" . CustomerAssignmentStatus::ACTIVE->value . "'"));
                if ($filter['value'] == true) {
                    $qb->andWhere("EXISTS ({$activeGreetingAssignmentQB->getSQL()})");
                } else {
                    $qb->andWhere("NOT EXISTS ({$activeGreetingAssignmentQB->getSQL()})");
                }
                unset($searchSchema['filters'][$key]);
            }
            if (($filter['column'] ?? null) === 'hasActiveFactFindingAssignment') {
                $activeFactFindingAssignmentQB = $this->getEntityManager()->getConnection()->createQueryBuilder();
                $activeFactFindingAssignmentQB->select('1')
                        ->from('FactFindingAssignment')
                        ->andWhere($activeFactFindingAssignmentQB->expr()->eq('FactFindingAssignment.Customer_id', 'Customer.id'))
                        ->andWhere($activeFactFindingAssignmentQB->expr()->eq('FactFindingAssignment.status', "'" . CustomerAssignmentStatus::ACTIVE->value . "'"));
                if ($filter['value'] == true) {
                    $qb->andWhere("EXISTS ({$activeFactFindingAssignmentQB->getSQL()})");
                } else {
                    $qb->andWhere("NOT EXISTS ({$activeFactFindingAssignmentQB->getSQL()})");
                }
                unset($searchSchema['filters'][$key]);
            }
            if (($filter['column'] ?? null) === 'hasActiveStrikingAssignment') {
                $activeStrikingAssignmentQB = $this->getEntityManager()->getConnection()->createQueryBuilder();
                $activeStrikingAssignmentQB->select('1')
                        ->from('StrikingAssignment')
                        ->andWhere($activeStrikingAssignmentQB->expr()->eq('StrikingAssignment.Customer_id', 'Customer.id'))
                        ->andWhere($activeStrikingAssignmentQB->expr()->eq('StrikingAssignment.status', "'" . CustomerAssignmentStatus::ACTIVE->value . "'"));
                if ($filter['value'] == true) {
                    $qb->andWhere("EXISTS ({$activeStrikingAssignmentQB->getSQL()})");
                } else {
                    $qb->andWhere("NOT EXISTS ({$activeStrikingAssignmentQB->getSQL()})");
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

    public function ofId(string $id): Customer
    {
        return $this->findOneByIdOrDie($id);
    }
    
    public function importFromCsvFile(string $insertIntoValues): void
    {
        $sql = <<<_SQL
INSERT IGNORE INTO Customer(name, phone, email, `source`)
VALUES $insertIntoValues
_SQL;
        $connection = $this->getEntityManager()->getConnection();
        $connection->executeQuery($sql);
    }
}
