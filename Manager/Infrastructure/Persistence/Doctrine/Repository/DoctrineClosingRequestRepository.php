<?php

namespace Manager\Infrastructure\Persistence\Doctrine\Repository;

use DateTime;
use Doctrine\DBAL\Query\QueryBuilder;
use Manager\Domain\Model\Manager\Sales\StrikingAssignment\ClosingRequest;
use Manager\Domain\Task\ClosingRequest\ClosingRequestRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineAllListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrinePaginationListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;
use Shared\Domain\Enum\ManagementApprovalStatus;

class DoctrineClosingRequestRepository extends DoctrineEntityRepository implements ClosingRequestRepository
{

    public function ofId(string $id): ClosingRequest
    {
        return $this->findOneByIdOrDie($id);
    }

    //

    protected function createCoreQueryBuilder(): QueryBuilder
    {
        return parent::createCoreQueryBuilder()
                        ->innerJoin('ClosingRequest', 'StrikingAssignment', 'StrikingAssignment',
                                'ClosingRequest.StrikingAssignment_id = StrikingAssignment.id')
                        ->innerJoin('StrikingAssignment', 'Sales', 'Sales', 'StrikingAssignment.Sales_id = Sales.id');
    }

    public function aClosingRequestBelongsToManager(string $managerId, string $id): ?array
    {
        $filters = [
            new Filter($managerId, 'Sales.Manager_id'),
            new Filter($id, 'ClosingRequest.id'),
        ];
        return $this->fetchOneBy($filters);
    }

    public function closingRequestListBelongsToManager(string $managerId, array $paginationSchema): array
    {
        $doctrinePaginationListCategory = DoctrinePaginationListCategory::fromSchema($paginationSchema)
                ->addFilter(new Filter($managerId, 'Sales.Manager_id'));
        return $this->fetchPaginationList($doctrinePaginationListCategory);
    }

    public function closingRequestCountBelongsToManager(string $managerId, array $searchSchema)
    {
        $qb = $this->dbalQueryBuilder();
        $qb->select("COUNT(*)")
                ->from('ClosingRequest')
                ->innerJoin('ClosingRequest', 'StrikingAssignment', 'StrikingAssignment',
                        'ClosingRequest.StrikingAssignment_id = StrikingAssignment.id')
                ->innerJoin('StrikingAssignment', 'Sales', 'Sales', 'StrikingAssignment.Sales_id = Sales.id')
                ->andWhere($qb->expr()->eq('Sales.Manager_id', ':managerId'))
                ->setParameter('managerId', $managerId);
        foreach ($searchSchema['filters'] ?? [] as $filterSchema) {
            Filter::fromSchema($filterSchema)->applyToQuery($qb);
        }
        return $qb->executeQuery()->fetchOne();
    }

    public function monthlyClosingCountBelongsToManager(string $managerId, array $searchSchema): array
    {
        $qb = $this->dbalQueryBuilder();
        $qb->select('COUNT(*) closingCount')
                ->addSelect('EXTRACT(YEAR_MONTH FROM ClosingRequest.createdTime) yearMonth')
                ->from('ClosingRequest')
                ->innerJoin('ClosingRequest', 'StrikingAssignment', 'StrikingAssignment',
                        'ClosingRequest.StrikingAssignment_id = StrikingAssignment.id')
                ->innerJoin('StrikingAssignment', 'Sales', 'Sales', 'StrikingAssignment.Sales_id = Sales.id')
                ->andWhere($qb->expr()->eq('Sales.Manager_id', ':managerId'))
                ->setParameter('managerId', $managerId)
                ->andWhere($qb->expr()->eq('ClosingRequest.status',
                                sprintf("'%s'", ManagementApprovalStatus::APPROVED->value)))
                ->groupBy('yearMonth');

        $startMonthDefined = false;
        $endMonthDefined = false;
        foreach ($searchSchema['filters'] ?? [] as $filter) {
            if ($filter['columns'] ?? null === 'yearMonth' && $filter['comparisonType'] ?? null === 'GTE') {
                $startMonthDefined = true;
                $qb->andWhere($qb->expr()->gte('EXTRACT(YEAR_MONTH FROM ClosingRequest.createdTime)',
                                $filter['value'] ?? (new DateTime('-12 months'))->format('Ym')));
                break;
            }
            if ($filter['columns'] ?? null === 'yearMonth' && $filter['comparisonType'] ?? null === 'LTE') {
                $endMonthDefined = true;
                $qb->andWhere($qb->expr()->lte('EXTRACT(YEAR_MONTH FROM ClosingRequest.createdTime)',
                                $filter['value'] ?? (new DateTime())->format('Ym')));
                break;
            }
        }

        if (!$startMonthDefined) {
            $qb->andWhere($qb->expr()->gte('EXTRACT(YEAR_MONTH FROM ClosingRequest.createdTime)',
                            (new DateTime('-12 months'))->format('Ym')));
        }
        if (!$endMonthDefined) {
            $qb->andWhere($qb->expr()->lte('EXTRACT(YEAR_MONTH FROM ClosingRequest.createdTime)',
                            (new DateTime())->format('Ym')));
        }
        return DoctrineAllListCategory::fromSchema()
                        ->fetchResult($qb);
    }

    public function monthlyTotalClosingBelongsToManager(string $managerId, array $searchSchema): array
    {
        $qb = $this->dbalQueryBuilder();
        $qb->select('SUM(ClosingRequest.transactionValue) totalTransaction')
                ->addSelect('EXTRACT(YEAR_MONTH FROM ClosingRequest.createdTime) yearMonth')
                ->from('ClosingRequest')
                ->innerJoin('ClosingRequest', 'StrikingAssignment', 'StrikingAssignment',
                        'ClosingRequest.StrikingAssignment_id = StrikingAssignment.id')
                ->innerJoin('StrikingAssignment', 'Sales', 'Sales', 'StrikingAssignment.Sales_id = Sales.id')
                ->andWhere($qb->expr()->eq('Sales.Manager_id', ':managerId'))
                ->setParameter('managerId', $managerId)
                ->andWhere($qb->expr()->eq('ClosingRequest.status',
                                sprintf("'%s'", ManagementApprovalStatus::APPROVED->value)))
                ->groupBy('yearMonth');

        $startMonthDefined = false;
        $endMonthDefined = false;
        foreach ($searchSchema['filters'] ?? [] as $filter) {
            if ($filter['columns'] ?? null === 'yearMonth' && $filter['comparisonType'] ?? null === 'GTE') {
                $startMonthDefined = true;
                $qb->andWhere($qb->expr()->gte('EXTRACT(YEAR_MONTH FROM ClosingRequest.createdTime)',
                                $filter['value'] ?? (new DateTime('-12 months'))->format('Ym')));
                break;
            }
            if ($filter['columns'] ?? null === 'yearMonth' && $filter['comparisonType'] ?? null === 'LTE') {
                $endMonthDefined = true;
                $qb->andWhere($qb->expr()->lte('EXTRACT(YEAR_MONTH FROM ClosingRequest.createdTime)',
                                $filter['value'] ?? (new DateTime())->format('Ym')));
                break;
            }
        }

        if (!$startMonthDefined) {
            $qb->andWhere($qb->expr()->gte('EXTRACT(YEAR_MONTH FROM ClosingRequest.createdTime)',
                            (new DateTime('-12 months'))->format('Ym')));
        }
        if (!$endMonthDefined) {
            $qb->andWhere($qb->expr()->lte('EXTRACT(YEAR_MONTH FROM ClosingRequest.createdTime)',
                            (new DateTime())->format('Ym')));
        }
        return DoctrineAllListCategory::fromSchema()
                        ->fetchResult($qb);
    }
}
