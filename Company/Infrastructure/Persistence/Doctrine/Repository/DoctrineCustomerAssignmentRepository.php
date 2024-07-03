<?php

namespace Company\Infrastructure\Persistence\Doctrine\Repository;

use Company\Domain\Task\CustomerAssignment\CustomerAssignmentRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrinePaginationListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;
use Shared\Domain\Enum\ManagementApprovalStatus;
use Shared\Domain\Enum\SalesActivityScheduleStatus;

class DoctrineCustomerAssignmentRepository extends DoctrineEntityRepository implements CustomerAssignmentRepository
{

    //
    public function aCustomerAssignment(string $id): array
    {
        return $this->queryOneById($id);
    }

    public function customerAssignmentList(array $paginationSchema): array
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
        
        $qb = $this->createCoreQueryBuilder()
                ->innerJoin('CustomerAssignment', 'Customer', 'Customer', 'CustomerAssignment.Customer_id = Customer.id')
                ->innerJoin('CustomerAssignment', 'Sales', 'Sales', 'CustomerAssignment.Sales_id = Sales.id')
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

    public function assignmentCount(array $searchSchema): ?int
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
                ->from('CustomerAssignment');
        
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
}
