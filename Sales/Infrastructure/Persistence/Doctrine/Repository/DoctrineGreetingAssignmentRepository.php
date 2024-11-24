<?php

namespace Sales\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\DBAL\Query\QueryBuilder;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrinePaginationListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;
use Sales\Domain\Model\Sales\GreetingAssignment;
use Sales\Domain\Task\GreetingAssignment\GreetingAssignmentRepository;
use Shared\Domain\Enum\SalesActivityScheduleStatus;

class DoctrineGreetingAssignmentRepository extends DoctrineEntityRepository implements GreetingAssignmentRepository
{

    public function ofId(string $id): GreetingAssignment
    {
        return $this->findOneByIdOrDie($id);
    }

    //
    public function aGreetingAssignmentBelongsToSales(string $salesId, string $id): array
    {
        $filters = [
            new Filter($salesId, 'GreetingAssignment.Sales_id'),
            new Filter($id, 'GreetingAssignment.id'),
        ];
        return $this->fetchOneBy($filters);
    }

    public function greetingAssignmentListBelongsToSales(string $salesId, array $paginationSchema): array
    {
        $hasSalesActivityScheduleSubquery = $this->dbalQueryBuilder();
        $hasSalesActivityScheduleSubquery->select("1")
                ->from('SalesActivitySchedule')
                ->where($hasSalesActivityScheduleSubquery->expr()->eq("SalesActivitySchedule.CustomerAssignment_id", "GreetingAssignment.CustomerAssignment_id"));
        
        $activeSalesActivityScheduleStatus = SalesActivityScheduleStatus::SCHEDULED->value;
        $hasActiveSalesActivityScheduleSubquery = $this->dbalQueryBuilder();
        $hasActiveSalesActivityScheduleSubquery->select("1")
                ->from('SalesActivitySchedule')
                ->where($hasActiveSalesActivityScheduleSubquery->expr()->eq("SalesActivitySchedule.CustomerAssignment_id", "GreetingAssignment.CustomerAssignment_id"))
                ->andWhere($hasActiveSalesActivityScheduleSubquery->expr()->eq("SalesActivitySchedule.status", "'{$activeSalesActivityScheduleStatus}'"));
        
        
        $qb = $this->createCoreQueryBuilder();
        $qb->andWhere($qb->expr()->eq('GreetingAssignment.Sales_id', ":salesId"))
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
        }
        
        return DoctrinePaginationListCategory::fromSchema($paginationSchema)
                ->paginateResult($qb, $this->getTableName());
    }

    public function totalGreetingAssignmentBelongsToSales(string $salesId, array $searchSchema): int
    {
        $hasSalesActivityScheduleSubquery = $this->dbalQueryBuilder();
        $hasSalesActivityScheduleSubquery->select("1")
                ->from('SalesActivitySchedule')
                ->where($hasSalesActivityScheduleSubquery->expr()->eq("SalesActivitySchedule.CustomerAssignment_id", "GreetingAssignment.CustomerAssignment_id"));
        
        $activeSalesActivityScheduleStatus = SalesActivityScheduleStatus::SCHEDULED->value;
        $hasActiveSalesActivityScheduleSubquery = $this->dbalQueryBuilder();
        $hasActiveSalesActivityScheduleSubquery->select("1")
                ->from('SalesActivitySchedule')
                ->where($hasActiveSalesActivityScheduleSubquery->expr()->eq("SalesActivitySchedule.CustomerAssignment_id", "GreetingAssignment.CustomerAssignment_id"))
                ->andWhere($hasActiveSalesActivityScheduleSubquery->expr()->eq("SalesActivitySchedule.status", "'{$activeSalesActivityScheduleStatus}'"));
        
        $qb = $this->dbalQueryBuilder();
        $qb->select('COUNT(GreetingAssignment.id)')
                ->from('GreetingAssignment')
                ->andWhere('GreetingAssignment.Sales_id = :salesId')
                ->setParameter('salesId', $salesId);
        
        foreach ($searchSchema['filters'] ?? [] as $filterSchema) {
            if ($filterSchema['column']  === 'hasSalesActivitySchedule') {
                $hasSalesActivitySchedule = $filterSchema['value'] ? "EXISTS" : "NOT EXISTS";
                $qb->andWhere($hasSalesActivitySchedule . sprintf("(%s)", $hasSalesActivityScheduleSubquery->getSQL()));
            } elseif($filterSchema['column']  === 'hasActiveSalesActivitySchedule') {
                $hasActiveSalesActivityScheduleCriteria = $filterSchema['value'] ? "EXISTS" : "NOT EXISTS";
                $qb->andWhere($hasActiveSalesActivityScheduleCriteria . sprintf("(%s)", $hasActiveSalesActivityScheduleSubquery->getSQL()));
            } else {
                Filter::fromSchema($filterSchema)->applyToQuery($qb);
            }
        }
        
        return $qb->executeQuery()->fetchOne();
    }
}
