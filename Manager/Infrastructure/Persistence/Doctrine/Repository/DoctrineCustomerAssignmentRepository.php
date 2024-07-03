<?php

namespace Manager\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\DBAL\Query\QueryBuilder;
use Manager\Domain\Model\Manager\Sales\CustomerAssignment;
use Manager\Domain\Task\CustomerAssignment\CustomerAssignmentRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrinePaginationListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;
use SharedContext\Domain\Enum\ManagementApprovalStatus;
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

    //
    protected function createManagerQueryBuilder(string $managerId): QueryBuilder
    {
        $qb = $this->createCoreQueryBuilder();
        return $qb->innerJoin('CustomerAssignment', 'Sales', 'Sales', 'CustomerAssignment.Sales_id = Sales.id')
                        ->andWhere($qb->expr()->eq('Sales.Manager_id', ':managerId'))
                        ->setParameter('managerId', $managerId);
    }

    public function aCustomerAssignmentBelongsToManager(string $managerId, string $id): array
    {
        return $this->retrieveOne(
                        $this->createManagerQueryBuilder($managerId), [new Filter($id, 'CustomerAssignment.id')]);
    }

    public function assignmentCountBelongsToManager(string $managerId, array $searchSchema): ?int
    {
        $hasSalesActivityScheduleSubquery = $this->dbalQueryBuilder();
        $hasSalesActivityScheduleSubquery->select("1")
                ->from('SalesActivitySchedule')
                ->where($hasSalesActivityScheduleSubquery->expr()->eq("SalesActivitySchedule.CustomerAssignment_id", "CustomerAssignment.id"));
        
        $activeSalesActivityScheduleStatus = SalesActivityScheduleStatus::SCHEDULED->value;
        $hasActiveSalesActivityScheduleSubquery = $this->dbalQueryBuilder();
        $hasActiveSalesActivityScheduleSubquery->select("1")
                ->from('SalesActivitySchedule')
                ->where($hasActiveSalesActivityScheduleSubquery->expr()->eq("SalesActivitySchedule.CustomerAssignment_id", "CustomerAssignment.id"))
                ->andWhere($hasActiveSalesActivityScheduleSubquery->expr()->eq("SalesActivitySchedule.status", "'{$activeSalesActivityScheduleStatus}'"));
        
        $pendingRequestStatus = ManagementApprovalStatus::WAITING_FOR_APPROVAL->value;
        
        $hasPendingRecycleRequestSubquery = $this->dbalQueryBuilder();
        $hasPendingRecycleRequestSubquery->select("1")
                ->from('RecycleRequest')
                ->where($hasPendingRecycleRequestSubquery->expr()->eq("RecycleRequest.CustomerAssignment_id", "CustomerAssignment.id"))
                ->andWhere($hasPendingRecycleRequestSubquery->expr()->eq("RecycleRequest.status", "'{$pendingRequestStatus}'"));
        
        $hasPendingClosingRequestSubquery = $this->dbalQueryBuilder();
        $hasPendingClosingRequestSubquery->select("1")
                ->from('ClosingRequest')
                ->where($hasPendingClosingRequestSubquery->expr()->eq("ClosingRequest.CustomerAssignment_id", "CustomerAssignment.id"))
                ->andWhere($hasPendingClosingRequestSubquery->expr()->eq("ClosingRequest.status", "'{$pendingRequestStatus}'"));
                
        $qb = $this->dbalQueryBuilder();
        $qb->select('COUNT(CustomerAssignment.id)')
                ->from('CustomerAssignment')
                ->innerJoin('CustomerAssignment', 'Sales', 'Sales', 'CustomerAssignment.Sales_id = Sales.id')
                ->andWhere($qb->expr()->eq('Sales.Manager_id', ':managerId'))
                ->setParameter('managerId', $managerId);
        
        foreach ($searchSchema['filters'] ?? [] as $filterSchema) {
            if ($filterSchema['column']  === 'hasSalesActivitySchedule') {
                $hasSalesActivitySchedule = $filterSchema['value'] ? "EXISTS" : "NOT EXISTS";
                $qb->andWhere($hasSalesActivitySchedule . sprintf("(%s)", $hasSalesActivityScheduleSubquery->getSQL()));
            } elseif($filterSchema['column']  === 'hasActiveSalesActivitySchedule') {
                $hasActiveSalesActivityScheduleCriteria = $filterSchema['value'] ? "EXISTS" : "NOT EXISTS";
                $qb->andWhere($hasActiveSalesActivityScheduleCriteria . sprintf("(%s)", $hasActiveSalesActivityScheduleSubquery->getSQL()));
            } elseif($filterSchema['column']  === 'hasPendingRecycleRequest'){
                $hasPendingRecycleRequestCriteria = $filterSchema['value'] ? "EXISTS" : "NOT EXISTS";
                $qb->andWhere($hasPendingRecycleRequestCriteria . sprintf("(%s)", $hasPendingRecycleRequestSubquery->getSQL()));
            } elseif($filterSchema['column']  === 'hasPendingClosingRequest'){
                $hasPendingClosingRequestCriteria = $filterSchema['value'] ? "EXISTS" : "NOT EXISTS";
                $qb->andWhere($hasPendingClosingRequestCriteria . sprintf("(%s)", $hasPendingClosingRequestSubquery->getSQL()));
            } else {
                Filter::fromSchema($filterSchema)->applyToQuery($qb);
            }
        }
        
        return $qb->executeQuery()->fetchOne();
    }

    public function customerAssignmentListBelongsToManager(string $managerId, array $paginationSchema): array
    {
        $hasSalesActivityScheduleSubquery = $this->dbalQueryBuilder();
        $hasSalesActivityScheduleSubquery->select("1")
                ->from('SalesActivitySchedule')
                ->where($hasSalesActivityScheduleSubquery->expr()->eq("SalesActivitySchedule.CustomerAssignment_id", "CustomerAssignment.id"));
        
        $activeSalesActivityScheduleStatus = SalesActivityScheduleStatus::SCHEDULED->value;
        $hasActiveSalesActivityScheduleSubquery = $this->dbalQueryBuilder();
        $hasActiveSalesActivityScheduleSubquery->select("1")
                ->from('SalesActivitySchedule')
                ->where($hasActiveSalesActivityScheduleSubquery->expr()->eq("SalesActivitySchedule.CustomerAssignment_id", "CustomerAssignment.id"))
                ->andWhere($hasActiveSalesActivityScheduleSubquery->expr()->eq("SalesActivitySchedule.status", "'{$activeSalesActivityScheduleStatus}'"));
        
        $pendingRequestStatus = ManagementApprovalStatus::WAITING_FOR_APPROVAL->value;
        
        $hasPendingRecycleRequestSubquery = $this->dbalQueryBuilder();
        $hasPendingRecycleRequestSubquery->select("1")
                ->from('RecycleRequest')
                ->where($hasPendingRecycleRequestSubquery->expr()->eq("RecycleRequest.CustomerAssignment_id", "CustomerAssignment.id"))
                ->andWhere($hasPendingRecycleRequestSubquery->expr()->eq("RecycleRequest.status", "'{$pendingRequestStatus}'"));
        
        $hasPendingClosingRequestSubquery = $this->dbalQueryBuilder();
        $hasPendingClosingRequestSubquery->select("1")
                ->from('ClosingRequest')
                ->where($hasPendingClosingRequestSubquery->expr()->eq("ClosingRequest.CustomerAssignment_id", "CustomerAssignment.id"))
                ->andWhere($hasPendingClosingRequestSubquery->expr()->eq("ClosingRequest.status", "'{$pendingRequestStatus}'"));
        
        $qb = $this->createManagerQueryBuilder($managerId)
                ->innerJoin('CustomerAssignment', 'Customer', 'Customer', 'CustomerAssignment.Customer_id = Customer.id')
                ->innerJoin('CustomerAssignment', 'CustomerJourney', 'CustomerJourney', 'CustomerAssignment.CustomerJourney_id = CustomerJourney.id');
        foreach ($paginationSchema['filters'] ?? [] as $key =>  $filterSchema) {
            if ($filterSchema['column']  === 'hasSalesActivitySchedule') {
                $hasSalesActivitySchedule = $filterSchema['value'] ? "EXISTS" : "NOT EXISTS";
                $qb->andWhere($hasSalesActivitySchedule . sprintf("(%s)", $hasSalesActivityScheduleSubquery->getSQL()));
                unset($paginationSchema['filters'][$key]);
            } elseif($filterSchema['column']  === 'hasActiveSalesActivitySchedule') {
                $hasActiveSalesActivityScheduleCriteria = $filterSchema['value'] ? "EXISTS" : "NOT EXISTS";
                $qb->andWhere($hasActiveSalesActivityScheduleCriteria . sprintf("(%s)", $hasActiveSalesActivityScheduleSubquery->getSQL()));
                unset($paginationSchema['filters'][$key]);
            }
            elseif($filterSchema['column']  === 'hasPendingRecycleRequest') {
                $hasPendingRecycleRequestCriteria = $filterSchema['value'] ? "EXISTS" : "NOT EXISTS";
                $qb->andWhere($hasPendingRecycleRequestCriteria . sprintf("(%s)", $hasPendingRecycleRequestSubquery->getSQL()));
                unset($paginationSchema['filters'][$key]);
            }
            elseif($filterSchema['column']  === 'hasPendingClosingRequest') {
                $hasPendingClosingRequestCriteria = $filterSchema['value'] ? "EXISTS" : "NOT EXISTS";
                $qb->andWhere($hasPendingClosingRequestCriteria . sprintf("(%s)", $hasPendingClosingRequestSubquery->getSQL()));
                unset($paginationSchema['filters'][$key]);
            }
        }
        
        return $doctrinePaginationListCategory = DoctrinePaginationListCategory::fromSchema($paginationSchema)
                ->paginateResult($qb, $this->getTableName());
    }
}
