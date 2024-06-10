<?php

namespace Sales\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\DBAL\Query\QueryBuilder;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrinePaginationListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;
use Sales\Domain\Model\Sales\CustomerAssignment;
use Sales\Domain\Task\BySales\CustomerAssignment\CustomerAssignmentRepository;
use SharedContext\Domain\Enum\SalesActivityScheduleStatus;

class DoctrineCustomerAssignmentRepository extends DoctrineEntityRepository implements CustomerAssignmentRepository
{

    public function add(CustomerAssignment $customerAssignment): void
    {
        $this->persist($customerAssignment);
    }

    public function ofId(string $id): CustomerAssignment
    {
        return $this->findOneByIdOrDie($id);
    }

    protected function createCoreQueryBuilder(): QueryBuilder
    {
        return parent::createCoreQueryBuilder()
            ->innerJoin('CustomerAssignment', 'Customer', 'Customer', 'CustomerAssignment.Customer_id = Customer.id');
    }

    public function aCustomerAssignmentBelongsToSales(string $salesId, string $id): array
    {
        $filters = [
            new Filter($salesId, 'CustomerAssignment.Sales_id'),
            new Filter($id, 'CustomerAssignment.id'),
        ];
        return $this->fetchOneBy($filters);
    }

    public function customerAssignmentListBelongsToSales(string $salesId, array $pageSchema): array
    {
        $newAssignmentSubquery = $this->dbalQueryBuilder();
        $newAssignmentSubquery->select("1")
                ->from('SalesActivitySchedule')
                ->where($newAssignmentSubquery->expr()->eq("SalesActivitySchedule.CustomerAssignment_id", "CustomerAssignment.id"));
        
        $activeSalesActivityScheduleStatus = SalesActivityScheduleStatus::SCHEDULED->value;
        $hasActiveSalesActivityScheduleSubquery = $this->dbalQueryBuilder();
        $hasActiveSalesActivityScheduleSubquery->select("1")
                ->from('SalesActivitySchedule')
                ->where($hasActiveSalesActivityScheduleSubquery->expr()->eq("SalesActivitySchedule.CustomerAssignment_id", "CustomerAssignment.id"))
                ->andWhere($hasActiveSalesActivityScheduleSubquery->expr()->eq("SalesActivitySchedule.status", "'{$activeSalesActivityScheduleStatus}'"));
        
        $qb = $this->createCoreQueryBuilder();
        foreach ($pageSchema['filters'] ?? [] as $key =>  $filterSchema) {
            if ($filterSchema['column']  === 'newAssignment') {
                $newAssignmentCriteria = $filterSchema['value'] ? "NOT EXISTS" : "EXISTS";
                $qb->andWhere($newAssignmentCriteria . sprintf("(%s)", $newAssignmentSubquery->getSQL()));
                unset($pageSchema['filters'][$key]);
            }
            elseif($filterSchema['column']  === 'hasActiveSalesActivitySchedule') {
                $hasActiveSalesActivityScheduleCriteria = $filterSchema['value'] ? "EXISTS" : "NOT EXISTS";
                $qb->andWhere($hasActiveSalesActivityScheduleCriteria . sprintf("(%s)", $hasActiveSalesActivityScheduleSubquery->getSQL()));
                unset($pageSchema['filters'][$key]);
            }
        }
        
        $doctrinePaginationListCategory = DoctrinePaginationListCategory::fromSchema($pageSchema)
                ->addFilter(new Filter($salesId, 'CustomerAssignment.Sales_id'));
        return $doctrinePaginationListCategory->paginateResult($qb, $this->getTableName());
    }
    
    public function totalCustomerAssignmentBelongsToSales(string $salesId, array $searchSchema): int
    {
        $newAssignmentSubquery = $this->dbalQueryBuilder();
        $newAssignmentSubquery->select("1")
                ->from('SalesActivitySchedule')
                ->where($newAssignmentSubquery->expr()->eq("SalesActivitySchedule.CustomerAssignment_id", "CustomerAssignment.id"));
        
        $activeSalesActivityScheduleStatus = SalesActivityScheduleStatus::SCHEDULED->value;
        $hasActiveSalesActivityScheduleSubquery = $this->dbalQueryBuilder();
        $hasActiveSalesActivityScheduleSubquery->select("1")
                ->from('SalesActivitySchedule')
                ->where($hasActiveSalesActivityScheduleSubquery->expr()->eq("SalesActivitySchedule.CustomerAssignment_id", "CustomerAssignment.id"))
                ->andWhere($hasActiveSalesActivityScheduleSubquery->expr()->eq("SalesActivitySchedule.status", "'{$activeSalesActivityScheduleStatus}'"));
                
        $qb = $this->dbalQueryBuilder();
        $qb->select('COUNT(CustomerAssignment.id)')
                ->from('CustomerAssignment')
                ->andWhere('CustomerAssignment.Sales_id = :salesId')
                ->setParameter('salesId', $salesId);
        
        foreach ($searchSchema['filters'] ?? [] as $filterSchema) {
            if ($filterSchema['column']  === 'newAssignment') {
                $newAssignmentCriteria = $filterSchema['value'] ? "NOT EXISTS" : "EXISTS";
                $qb->andWhere($newAssignmentCriteria . sprintf("(%s)", $newAssignmentSubquery->getSQL()));
            }
            elseif($filterSchema['column']  === 'hasActiveSalesActivitySchedule') {
                $hasActiveSalesActivityScheduleCriteria = $filterSchema['value'] ? "EXISTS" : "NOT EXISTS";
                $qb->andWhere($hasActiveSalesActivityScheduleCriteria . sprintf("(%s)", $hasActiveSalesActivityScheduleSubquery->getSQL()));
            } else {
                Filter::fromSchema($filterSchema)->applyToQuery($qb);
            }
        }
        
        return $qb->executeQuery()->fetchOne();
    }
}
