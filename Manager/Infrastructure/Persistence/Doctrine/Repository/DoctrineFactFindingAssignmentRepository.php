<?php

namespace Manager\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\DBAL\Query\QueryBuilder;
use Manager\Domain\Model\Manager\Sales\FactFindingAssignment;
use Manager\Domain\Task\FactFindingAssignment\FactFindingAssignmentRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrinePaginationListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;
use Shared\Domain\Enum\SalesActivityScheduleStatus;

class DoctrineFactFindingAssignmentRepository extends DoctrineEntityRepository
        implements FactFindingAssignmentRepository
{

    public function add(FactFindingAssignment $factFindingAssignment): void
    {
        $this->persist($factFindingAssignment);
    }

    //
    
    protected function createCoreQueryBuilder(): QueryBuilder
    {
        return parent::createCoreQueryBuilder()
                ->innerJoin('FactFindingAssignment', 'Sales', 'Sales', 'FactFindingAssignment.Sales_id = Sales.id');
    }
    public function aCustomerAssignmentBelongsByManager(string $managerId, string $id): array
    {
        $filters = [
            new Filter($managerId, 'Sales.Manager_id'),
            new Filter($id, 'FactFindingAssignment.id'),
        ];
        return $this->fetchOneBy($filters);
    }

    public function assignmentCountBelongsByManager(string $managerId, array $searchSchema): ?int
    {
        $hasSalesActivityScheduleSubquery = $this->dbalQueryBuilder();
        $hasSalesActivityScheduleSubquery->select("1")
                ->from('SalesActivitySchedule')
                ->where($hasSalesActivityScheduleSubquery->expr()->eq("SalesActivitySchedule.CustomerAssignment_id",
                                "FactFindingAssignment.CustomerAssignment_id"));

        $activeSalesActivityScheduleStatus = SalesActivityScheduleStatus::SCHEDULED->value;
        $hasActiveSalesActivityScheduleSubquery = $this->dbalQueryBuilder();
        $hasActiveSalesActivityScheduleSubquery->select("1")
                ->from('SalesActivitySchedule')
                ->where($hasActiveSalesActivityScheduleSubquery->expr()->eq("SalesActivitySchedule.CustomerAssignment_id",
                                "FactFindingAssignment.CustomerAssignment_id"))
                ->andWhere($hasActiveSalesActivityScheduleSubquery->expr()->eq("SalesActivitySchedule.status",
                                "'{$activeSalesActivityScheduleStatus}'"));

        $qb = $this->dbalQueryBuilder();
        $qb->select('COUNT(FactFindingAssignment.id)')
                ->from('FactFindingAssignment')
                ->innerJoin('FactFindingAssignment', 'Sales', 'Sales', 'FactFindingAssignment.Sales_id = Sales.id')
                ->andWhere($qb->expr()->eq('Sales.Manager_id', ':managerId'))
                ->setParameter('managerId', $managerId);

        foreach ($searchSchema['filters'] ?? [] as $filterSchema) {
            if ($filterSchema['column'] === 'hasSalesActivitySchedule') {
                $hasSalesActivitySchedule = $filterSchema['value'] ? "EXISTS" : "NOT EXISTS";
                $qb->andWhere($hasSalesActivitySchedule . sprintf("(%s)", $hasSalesActivityScheduleSubquery->getSQL()));
            } elseif ($filterSchema['column'] === 'hasActiveSalesActivitySchedule') {
                $hasActiveSalesActivityScheduleCriteria = $filterSchema['value'] ? "EXISTS" : "NOT EXISTS";
                $qb->andWhere($hasActiveSalesActivityScheduleCriteria . sprintf("(%s)",
                                $hasActiveSalesActivityScheduleSubquery->getSQL()));
            } else {
                Filter::fromSchema($filterSchema)->applyToQuery($qb);
            }
        }

        return $qb->executeQuery()->fetchOne();
    }

    public function customerAssignmentListBelongsByManager(string $managerId, array $paginationSchema): array
    {
        $hasSalesActivityScheduleSubquery = $this->dbalQueryBuilder();
        $hasSalesActivityScheduleSubquery->select("1")
                ->from('SalesActivitySchedule')
                ->where($hasSalesActivityScheduleSubquery->expr()->eq("SalesActivitySchedule.CustomerAssignment_id",
                                "FactFindingAssignment.CustomerAssignment_id"));

        $activeSalesActivityScheduleStatus = SalesActivityScheduleStatus::SCHEDULED->value;
        $hasActiveSalesActivityScheduleSubquery = $this->dbalQueryBuilder();
        $hasActiveSalesActivityScheduleSubquery->select("1")
                ->from('SalesActivitySchedule')
                ->where($hasActiveSalesActivityScheduleSubquery->expr()->eq("SalesActivitySchedule.CustomerAssignment_id",
                                "FactFindingAssignment.CustomerAssignment_id"))
                ->andWhere($hasActiveSalesActivityScheduleSubquery->expr()->eq("SalesActivitySchedule.status",
                                "'{$activeSalesActivityScheduleStatus}'"));

        $qb = $this->createCoreQueryBuilder();
        $qb->andWhere($qb->expr()->eq('Sales.Manager_id', ':managerId'))
                ->setParameter('managerId', $managerId);
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
            }
        }

        return $doctrinePaginationListCategory = DoctrinePaginationListCategory::fromSchema($paginationSchema)
                ->paginateResult($qb, $this->getTableName());
    }
}
