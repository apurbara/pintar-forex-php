<?php

namespace Company\Infrastructure\Persistence\Doctrine\Repository;

use Company\Domain\Model\Sales\CustomerAssignment\ClosingRequest;
use Company\Domain\Task\InCompany\ClosingRequest\ClosingRequestRepository;
use DateTime;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineAllListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use SharedContext\Domain\Enum\ManagementApprovalStatus;

class DoctrineClosingRequestRepository extends DoctrineEntityRepository implements ClosingRequestRepository
{

    public function ofId(string $id): ClosingRequest
    {
        return $this->findOneByIdOrDie($id);
    }

    //
    public function aClosingRequest(string $id): ?array
    {
        return $this->queryOneById($id);
    }

    public function closingRequestList(array $paginationSchema): array
    {
        return $this->queryPaginationList($paginationSchema);
    }

    public function monthlyClosingCount(array $searchSchema): array
    {
        $qb = $this->dbalQueryBuilder();
        $qb->select('COUNT(*) closingCount')
                ->addSelect('EXTRACT(YEAR_MONTH FROM ClosingRequest.createdTime) yearMonth')
                ->from('ClosingRequest')
                ->andWhere($qb->expr()->eq('ClosingRequest.status', sprintf("'%s'", ManagementApprovalStatus::APPROVED->value)))
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
                $qb->andWhere($qb->expr()->lte('EXTRACT(YEAR_MONTH FROM ClosingRequest.createdTime)', $filter['value'] ?? (new DateTime())->format('Ym')));
                break;
            }
        }
        
        if (!$startMonthDefined) {
            $qb->andWhere($qb->expr()->gte('EXTRACT(YEAR_MONTH FROM ClosingRequest.createdTime)', (new DateTime('-12 months'))->format('Ym')));
        }
        if (!$endMonthDefined) {
            $qb->andWhere($qb->expr()->lte('EXTRACT(YEAR_MONTH FROM ClosingRequest.createdTime)', (new DateTime())->format('Ym')));
        }
        return DoctrineAllListCategory::fromSchema()
                ->fetchResult($qb);
    }

    public function monthlyTotalClosing(array $searchSchema): array
    {
        $qb = $this->dbalQueryBuilder();
        $qb->select('SUM(ClosingRequest.transactionValue) totalTransaction')
                ->addSelect('EXTRACT(YEAR_MONTH FROM ClosingRequest.createdTime) yearMonth')
                ->from('ClosingRequest')
                ->andWhere($qb->expr()->eq('ClosingRequest.status', sprintf("'%s'", ManagementApprovalStatus::APPROVED->value)))
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
                $qb->andWhere($qb->expr()->lte('EXTRACT(YEAR_MONTH FROM ClosingRequest.createdTime)', $filter['value'] ?? (new DateTime())->format('Ym')));
                break;
            }
        }
        
        if (!$startMonthDefined) {
            $qb->andWhere($qb->expr()->gte('EXTRACT(YEAR_MONTH FROM ClosingRequest.createdTime)', (new DateTime('-12 months'))->format('Ym')));
        }
        if (!$endMonthDefined) {
            $qb->andWhere($qb->expr()->lte('EXTRACT(YEAR_MONTH FROM ClosingRequest.createdTime)', (new DateTime())->format('Ym')));
        }
        return DoctrineAllListCategory::fromSchema()
                ->fetchResult($qb);
    }
}
