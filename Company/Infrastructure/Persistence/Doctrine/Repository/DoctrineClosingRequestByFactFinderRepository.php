<?php

namespace Company\Infrastructure\Persistence\Doctrine\Repository;

use DateTime;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineAllListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;
use Shared\Domain\Enum\ManagementApprovalStatus;

class DoctrineClosingRequestByFactFinderRepository extends DoctrineEntityRepository
{

    public function aClosingRequestByFactFinder(string $id): ?array
    {
        return $this->queryOneById($id);
    }

    public function closingRequestByFactFinderList(array $paginationSchema): array
    {
        return $this->queryPaginationList($paginationSchema);
    }

    public function monthlyClosingCount(array $searchSchema): array
    {
        $qb = $this->dbalQueryBuilder();
        $qb->select('COUNT(*) closingCount')
                ->addSelect('EXTRACT(YEAR_MONTH FROM ClosingRequestByFactFinder.createdTime) yearMonth')
                ->from('ClosingRequestByFactFinder')
                ->andWhere($qb->expr()->eq('ClosingRequestByFactFinder.status', sprintf("'%s'", ManagementApprovalStatus::APPROVED->value)))
                ->groupBy('yearMonth');
        
        $startMonthDefined = false;
        $endMonthDefined = false;
        foreach ($searchSchema['filters'] ?? [] as $filter) {
            if ($filter['columns'] ?? null === 'yearMonth' && $filter['comparisonType'] ?? null === 'GTE') {
                $startMonthDefined = true;
                $qb->andWhere($qb->expr()->gte('EXTRACT(YEAR_MONTH FROM ClosingRequestByFactFinder.createdTime)',
                                $filter['value'] ?? (new DateTime('-12 months'))->format('Ym')));
                break;
            }
            if ($filter['columns'] ?? null === 'yearMonth' && $filter['comparisonType'] ?? null === 'LTE') {
                $endMonthDefined = true;
                $qb->andWhere($qb->expr()->lte('EXTRACT(YEAR_MONTH FROM ClosingRequestByFactFinder.createdTime)', $filter['value'] ?? (new DateTime())->format('Ym')));
                break;
            }
        }
        
        if (!$startMonthDefined) {
            $qb->andWhere($qb->expr()->gte('EXTRACT(YEAR_MONTH FROM ClosingRequestByFactFinder.createdTime)', (new DateTime('-12 months'))->format('Ym')));
        }
        if (!$endMonthDefined) {
            $qb->andWhere($qb->expr()->lte('EXTRACT(YEAR_MONTH FROM ClosingRequestByFactFinder.createdTime)', (new DateTime())->format('Ym')));
        }
        return DoctrineAllListCategory::fromSchema()
                ->fetchResult($qb);
    }

    public function monthlyTotalClosing(array $searchSchema): array
    {
        $qb = $this->dbalQueryBuilder();
        $qb->select('SUM(ClosingRequestByFactFinder.transactionValue) totalTransaction')
                ->addSelect('EXTRACT(YEAR_MONTH FROM ClosingRequestByFactFinder.createdTime) yearMonth')
                ->from('ClosingRequestByFactFinder')
                ->andWhere($qb->expr()->eq('ClosingRequestByFactFinder.status', sprintf("'%s'", ManagementApprovalStatus::APPROVED->value)))
                ->groupBy('yearMonth');
        
        $startMonthDefined = false;
        $endMonthDefined = false;
        foreach ($searchSchema['filters'] ?? [] as $filter) {
            if ($filter['columns'] ?? null === 'yearMonth' && $filter['comparisonType'] ?? null === 'GTE') {
                $startMonthDefined = true;
                $qb->andWhere($qb->expr()->gte('EXTRACT(YEAR_MONTH FROM ClosingRequestByFactFinder.createdTime)',
                                $filter['value'] ?? (new DateTime('-12 months'))->format('Ym')));
                break;
            }
            if ($filter['columns'] ?? null === 'yearMonth' && $filter['comparisonType'] ?? null === 'LTE') {
                $endMonthDefined = true;
                $qb->andWhere($qb->expr()->lte('EXTRACT(YEAR_MONTH FROM ClosingRequestByFactFinder.createdTime)', $filter['value'] ?? (new DateTime())->format('Ym')));
                break;
            }
        }
        
        if (!$startMonthDefined) {
            $qb->andWhere($qb->expr()->gte('EXTRACT(YEAR_MONTH FROM ClosingRequestByFactFinder.createdTime)', (new DateTime('-12 months'))->format('Ym')));
        }
        if (!$endMonthDefined) {
            $qb->andWhere($qb->expr()->lte('EXTRACT(YEAR_MONTH FROM ClosingRequestByFactFinder.createdTime)', (new DateTime())->format('Ym')));
        }
        return DoctrineAllListCategory::fromSchema()
                ->fetchResult($qb);
    }

    public function closingRequestByFactFinderCount(array $searchSchema)
    {
        $qb = $this->dbalQueryBuilder();
        $qb->select("COUNT(*)")
                ->from('ClosingRequestByFactFinder');
        foreach ($searchSchema['filters'] ?? [] as $filterSchema) {
            Filter::fromSchema($filterSchema)->applyToQuery($qb);
        }
        return $qb->executeQuery()->fetchOne();
    }
}
