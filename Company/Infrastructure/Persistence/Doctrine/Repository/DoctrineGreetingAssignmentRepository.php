<?php

namespace Company\Infrastructure\Persistence\Doctrine\Repository;

use Company\Application\EventHandler\GreetingAssignmentRepository as GreetingAssignmentRepository2;
use Company\Domain\Model\Manager\Sales\GreetingAssignment;
use Company\Domain\Task\CustomerAssignment\GreetingAssignmentRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrinePaginationListCategory;
use SebastianBergmann\CodeCoverage\Filter;
use Shared\Domain\Enum\SalesActivityScheduleStatus;

class DoctrineGreetingAssignmentRepository extends DoctrineEntityRepository implements GreetingAssignmentRepository, GreetingAssignmentRepository2
{

    public function ofId(string $id): GreetingAssignment
    {
        return $this->findOneByIdOrDie($id);
    }

    public function add(GreetingAssignment $greetingAssignment): void
    {
        $this->persist($greetingAssignment);
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
                                "GreetingAssignment.CustomerAssignment_id"));

        $activeSalesActivityScheduleStatus = SalesActivityScheduleStatus::SCHEDULED->value;
        $hasActiveSalesActivityScheduleSubquery = $this->dbalQueryBuilder();
        $hasActiveSalesActivityScheduleSubquery->select("1")
                ->from('SalesActivitySchedule')
                ->where($hasActiveSalesActivityScheduleSubquery->expr()->eq("SalesActivitySchedule.CustomerAssignment_id",
                                "GreetingAssignment.CustomerAssignment_id"))
                ->andWhere($hasActiveSalesActivityScheduleSubquery->expr()->eq("SalesActivitySchedule.status",
                                "'{$activeSalesActivityScheduleStatus}'"));

        $qb = $this->createCoreQueryBuilder()
                ->innerJoin('GreetingAssignment', 'Customer', 'Customer', 'GreetingAssignment.Customer_id = Customer.id')
                ->innerJoin('GreetingAssignment', 'Sales', 'Sales', 'GreetingAssignment.Sales_id = Sales.id');
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

    public function assignmentCount(array $searchSchema): ?int
    {
        $hasSalesActivityScheduleSubquery = $this->dbalQueryBuilder();
        $hasSalesActivityScheduleSubquery->select("1")
                ->from('SalesActivitySchedule')
                ->where($hasSalesActivityScheduleSubquery->expr()->eq("SalesActivitySchedule.CustomerAssignment_id",
                                "GreetingAssignment.CustomerAssignment_id"));

        $activeSalesActivityScheduleStatus = SalesActivityScheduleStatus::SCHEDULED->value;
        $hasActiveSalesActivityScheduleSubquery = $this->dbalQueryBuilder();
        $hasActiveSalesActivityScheduleSubquery->select("1")
                ->from('SalesActivitySchedule')
                ->where($hasActiveSalesActivityScheduleSubquery->expr()->eq("SalesActivitySchedule.CustomerAssignment_id",
                                "GreetingAssignment.CustomerAssignment.id"))
                ->andWhere($hasActiveSalesActivityScheduleSubquery->expr()->eq("SalesActivitySchedule.status",
                                "'{$activeSalesActivityScheduleStatus}'"));

        $qb = $this->dbalQueryBuilder();
        $qb->select('COUNT(GreetingAssignment.id)')
                ->from('GreetingAssignment');

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
}
