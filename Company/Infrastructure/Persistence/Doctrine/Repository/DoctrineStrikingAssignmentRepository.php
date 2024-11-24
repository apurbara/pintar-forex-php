<?php

namespace Company\Infrastructure\Persistence\Doctrine\Repository;

use Company\Application\EventHandler\StrikingAssignmentRepository as StrikingAssignmentRepository2;
use Company\Domain\Model\Manager\Sales\StrikingAssignment;
use Company\Domain\Task\CustomerAssignment\StrikingAssignmentRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrinePaginationListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;
use Shared\Domain\Enum\ManagementApprovalStatus;
use Shared\Domain\Enum\SalesActivityScheduleStatus;

class DoctrineStrikingAssignmentRepository extends DoctrineEntityRepository implements StrikingAssignmentRepository, StrikingAssignmentRepository2
{

    public function ofId(string $id): StrikingAssignment
    {
        return $this->findOneByIdOrDie($id);
    }

    public function add(StrikingAssignment $strikingAssignment): void
    {
        $this->persist($strikingAssignment);
    }

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
                ->where($hasSalesActivityScheduleSubquery->expr()->eq("SalesActivitySchedule.CustomerAssignment_id",
                                "StrikingAssignment.CustomerAssignment_id"));

        $activeSalesActivityScheduleStatus = SalesActivityScheduleStatus::SCHEDULED->value;
        $hasActiveSalesActivityScheduleSubquery = $this->dbalQueryBuilder();
        $hasActiveSalesActivityScheduleSubquery->select("1")
                ->from('SalesActivitySchedule')
                ->where($hasActiveSalesActivityScheduleSubquery->expr()->eq("SalesActivitySchedule.CustomerAssignment_id",
                                "StrikingAssignment.CustomerAssignment_id"))
                ->andWhere($hasActiveSalesActivityScheduleSubquery->expr()->eq("SalesActivitySchedule.status",
                                "'{$activeSalesActivityScheduleStatus}'"));

        $pendingRequestStatus = ManagementApprovalStatus::WAITING_FOR_APPROVAL->value;
        $hasPendingClosingRequestSubquery = $this->dbalQueryBuilder();
        $hasPendingClosingRequestSubquery->select("1")
                ->from('ClosingRequest')
                ->where($hasPendingClosingRequestSubquery->expr()->eq("ClosingRequest.StrikingAssignment_id",
                                "StrikingAssignment.id"))
                ->andWhere($hasPendingClosingRequestSubquery->expr()->eq("ClosingRequest.status",
                                "'{$pendingRequestStatus}'"));

        $qb = $this->createCoreQueryBuilder()
                ->innerJoin('StrikingAssignment', 'Customer', 'Customer', 'StrikingAssignment.Customer_id = Customer.id')
                ->innerJoin('StrikingAssignment', 'Sales', 'Sales', 'StrikingAssignment.Sales_id = Sales.id')
                ->leftJoin('StrikingAssignment', 'CustomerJourney', 'CustomerJourney',
                        'StrikingAssignment.CustomerJourney_id = CustomerJourney.id');
        foreach ($paginationSchema['filters'] ?? [] as $key => $filterSchema) {
            if ($filterSchema['column'] === 'hasSalesActivitySchedule') {
                $hasSalesActivitySchedule = $filterSchema['value'] ? "EXISTS" : "NOT EXISTS";
                $qb->andWhere($hasSalesActivitySchedule . sprintf("(%s)", $hasSalesActivityScheduleSubquery->getSQL()));
                unset($paginationSchema['filters'][$key]);
            } elseif ($filterSchema['column'] === 'hasActiveSalesActivitySchedule') {
                $hasActiveSalesActivityScheduleCriteria = $filterSchema['value'] ? "EXISTS" : "NOT EXISTS";
                $qb->andWhere($hasActiveSalesActivityScheduleCriteria . sprintf("(%s)",
                                $hasActiveSalesActivityScheduleSubquery->getSQL()));
                unset($paginationSchema['filters'][$key]);
            } elseif ($filterSchema['column'] === 'hasPendingClosingRequest') {
                $hasPendingClosingRequestCriteria = $filterSchema['value'] ? "EXISTS" : "NOT EXISTS";
                $qb->andWhere($hasPendingClosingRequestCriteria . sprintf("(%s)",
                                $hasPendingClosingRequestSubquery->getSQL()));
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
                ->where($hasSalesActivityScheduleSubquery->expr()->eq("SalesActivitySchedule.CustomerAssignment_id",
                                "StrikingAssignment.CustomerAssignment_id"));

        $activeSalesActivityScheduleStatus = SalesActivityScheduleStatus::SCHEDULED->value;
        $hasActiveSalesActivityScheduleSubquery = $this->dbalQueryBuilder();
        $hasActiveSalesActivityScheduleSubquery->select("1")
                ->from('SalesActivitySchedule')
                ->where($hasActiveSalesActivityScheduleSubquery->expr()->eq("SalesActivitySchedule.CustomerAssignment_id",
                                "StrikingAssignment.CustomerAssignment_id"))
                ->andWhere($hasActiveSalesActivityScheduleSubquery->expr()->eq("SalesActivitySchedule.status",
                                "'{$activeSalesActivityScheduleStatus}'"));

        $pendingRequestStatus = ManagementApprovalStatus::WAITING_FOR_APPROVAL->value;

        $hasPendingClosingRequestSubquery = $this->dbalQueryBuilder();
        $hasPendingClosingRequestSubquery->select("1")
                ->from('ClosingRequest')
                ->where($hasPendingClosingRequestSubquery->expr()->eq("ClosingRequest.StrikingAssignment_id",
                                "StrikingAssignment.id"))
                ->andWhere($hasPendingClosingRequestSubquery->expr()->eq("ClosingRequest.status",
                                "'{$pendingRequestStatus}'"));

        $qb = $this->dbalQueryBuilder();
        $qb->select('COUNT(StrikingAssignment.id)')
                ->from('StrikingAssignment');

        foreach ($searchSchema['filters'] ?? [] as $filterSchema) {
            if ($filterSchema['column'] === 'hasSalesActivitySchedule') {
                $hasSalesActivitySchedule = $filterSchema['value'] ? "EXISTS" : "NOT EXISTS";
                $qb->andWhere($hasSalesActivitySchedule . sprintf("(%s)", $hasSalesActivityScheduleSubquery->getSQL()));
            } elseif ($filterSchema['column'] === 'hasActiveSalesActivitySchedule') {
                $hasActiveSalesActivityScheduleCriteria = $filterSchema['value'] ? "EXISTS" : "NOT EXISTS";
                $qb->andWhere($hasActiveSalesActivityScheduleCriteria . sprintf("(%s)",
                                $hasActiveSalesActivityScheduleSubquery->getSQL()));
            } elseif ($filterSchema['column'] === 'hasPendingClosingRequest') {
                $hasPendingClosingRequestCriteria = $filterSchema['value'] ? "EXISTS" : "NOT EXISTS";
                $qb->andWhere($hasPendingClosingRequestCriteria . sprintf("(%s)",
                                $hasPendingClosingRequestSubquery->getSQL()));
            } else {
                Filter::fromSchema($filterSchema)->applyToQuery($qb);
            }
        }

        return $qb->executeQuery()->fetchOne();
    }
}
