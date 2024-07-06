<?php

namespace Sales\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\DBAL\Query\QueryBuilder;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrinePaginationListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;
use Sales\Domain\Model\Sales\CustomerAssignment;
use Sales\Domain\Task\CustomerAssignment\CustomerAssignmentRepository;
use Shared\Domain\Enum\ManagementApprovalStatus;
use Shared\Domain\Enum\SalesActivityScheduleStatus;

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

    public function customerAssignmentListBelongsToSales(string $salesId, array $paginationSchema): array
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
        
        $qb = $this->createCoreQueryBuilder();
        $qb->andWhere($qb->expr()->eq('CustomerAssignment.Sales_id', ":salesId"))
                ->setParameter('salesId', $salesId);
        
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
        
        return DoctrinePaginationListCategory::fromSchema($paginationSchema)
                ->paginateResult($qb, $this->getTableName());
    }
    
    public function totalCustomerAssignmentBelongsToSales(string $salesId, array $searchSchema): int
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
                ->andWhere('CustomerAssignment.Sales_id = :salesId')
                ->setParameter('salesId', $salesId);
        
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
