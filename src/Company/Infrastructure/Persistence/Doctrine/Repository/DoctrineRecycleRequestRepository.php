<?php

namespace Company\Infrastructure\Persistence\Doctrine\Repository;

use Company\Domain\Model\Sales\CustomerAssignment\RecycleRequest;
use Company\Domain\Task\InCompany\RecycleRequest\RecycleRequestRepository;
use DateTime;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineAllListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use SharedContext\Domain\Enum\ManagementApprovalStatus;

class DoctrineRecycleRequestRepository extends DoctrineEntityRepository implements RecycleRequestRepository
{

    public function ofId(string $id): RecycleRequest
    {
        return $this->findOneByIdOrDie($id);
    }

    //
    public function aRecycleRequest(string $id): ?array
    {
        return $this->queryOneById($id);
    }

    public function monthlyRecycledCount(array $searchSchema): array
    {
        $qb = $this->dbalQueryBuilder();
        $qb->select('COUNT(*) recycledCount')
                ->addSelect('EXTRACT(YEAR_MONTH FROM RecycleRequest.createdTime) yearMonth')
                ->from('RecycleRequest')
                ->andWhere($qb->expr()->eq('RecycleRequest.status', ManagementApprovalStatus::APPROVED->value))
                ->groupBy('yearMonth');
        
        $startMonthDefined = false;
        $endMonthDefined = false;
        foreach ($searchSchema['filters'] ?? [] as $filter) {
            if ($filter['columns'] ?? null === 'yearMonth' && $filter['comparisonType'] ?? null === 'GTE') {
                $startMonthDefined = true;
                $qb->andWhere($qb->expr()->gte('EXTRACT(YEAR_MONTH FROM RecycleRequest.createdTime)',
                                $filter['value'] ?? (new DateTime('-12 months'))->format('Ym')));
                break;
            }
            if ($filter['columns'] ?? null === 'yearMonth' && $filter['comparisonType'] ?? null === 'LTE') {
                $endMonthDefined = true;
                $qb->andWhere($qb->expr()->lte('EXTRACT(YEAR_MONTH FROM RecycleRequest.createdTime)', $filter['value'] ?? (new DateTime())->format('Ym')));
                break;
            }
        }
        
        if (!$startModifiedTimeDefined) {
            $qb->andWhere($qb->expr()->gte('EXTRACT(YEAR_MONTH FROM RecycleRequest.createdTime', (new DateTime('-12 months'))->format('Ym')));
        }
        if (!$endModifiedTimeDefined) {
            $qb->andWhere($qb->expr()->lte('EXTRACT(YEAR_MONTH FROM RecycleRequest.createdTime', (new DateTime())->format('Ym')));
        }
        return DoctrineAllListCategory::fromSchema()
                ->fetchResult($qb);
    }

    public function recycleRequestList(array $paginationSchema): array
    {
        return $this->queryPaginationList($paginationSchema);
    }
}
