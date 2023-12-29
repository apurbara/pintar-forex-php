<?php

namespace Manager\Infrastructure\Persistence\Doctrine\Repository;

use DateTime;
use Doctrine\DBAL\Query\QueryBuilder;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer\ClosingRequest;
use Manager\Domain\Task\ClosingRequest\ClosingRequestRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineAllListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrinePaginationListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;
use SharedContext\Domain\Enum\ManagementApprovalStatus;

class DoctrineClosingRequestRepository extends DoctrineEntityRepository implements ClosingRequestRepository
{

    public function ofId(string $id): ClosingRequest
    {
        return $this->findOneByIdOrDie($id);
    }

    //
    protected function generateManagerAggergateQueryBuilder(): QueryBuilder
    {
        return $this->createCoreQueryBuilder()
                        ->innerJoin('ClosingRequest', 'AssignedCustomer', 'AssignedCustomer',
                                'ClosingRequest.AssignedCustomer_id = AssignedCustomer.id')
                        ->innerJoin('AssignedCustomer', 'Sales', 'Sales', 'AssignedCustomer.Sales_id = Sales.id');
    }

    public function aClosingRequestBelongToManager(string $managerId, string $id): ?array
    {
        return $this->retrieveOneOrDie($this->generateManagerAggergateQueryBuilder(),
                        [
                    new Filter($managerId, 'Sales.Manager_id'),
                    new Filter($id, 'ClosingRequest.id'),
        ]);
    }

    public function closingRequestListBelongToManager(string $managerId, array $paginationSchema): array
    {
        return DoctrinePaginationListCategory::fromSchema($paginationSchema)
                ->addFilter(new Filter($managerId, 'Sales.Manager_id'))
                ->paginateResult($this->generateManagerAggergateQueryBuilder(), $this->getTableName());
    }

    public function monthlyTotalClosing(string $managerId, array $searchSchema): array
    {
        $qb = $this->dbalQueryBuilder();
        $qb->select('SUM(ClosingRequest.transactionValue) totalTransaction')
                ->addSelect('EXTRACT(YEAR_MONTH FROM ClosingRequest.createdTime) yearMonth')
                ->from('ClosingRequest')
                ->innerJoin('ClosingRequest', 'AssignedCustomer', 'AssignedCustomer',
                        'ClosingRequest.AssignedCustomer_id = AssignedCustomer.id')
                ->innerJoin('AssignedCustomer', 'Sales', 'Sales', 'AssignedCustomer.Sales_id = Sales.id')
                ->andWhere($qb->expr()->eq('ClosingRequest.status', "'" .  ManagementApprovalStatus::APPROVED->value . "'"))
                ->andWhere($qb->expr()->eq('Sales.Manager_id', ':managerId'))
                ->setParameter('managerId', $managerId)
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

    public function monthlyClosingCount(string $managerId, array $searchSchema): array
    {
        $qb = $this->dbalQueryBuilder();
        $qb->select('COUNT(*) closingCount')
                ->addSelect('EXTRACT(YEAR_MONTH FROM ClosingRequest.createdTime) yearMonth')
                ->from('ClosingRequest')
                ->innerJoin('ClosingRequest', 'AssignedCustomer', 'AssignedCustomer',
                        'ClosingRequest.AssignedCustomer_id = AssignedCustomer.id')
                ->innerJoin('AssignedCustomer', 'Sales', 'Sales', 'AssignedCustomer.Sales_id = Sales.id')
                ->andWhere($qb->expr()->eq('ClosingRequest.status', "'" .  ManagementApprovalStatus::APPROVED->value . "'"))
                ->andWhere($qb->expr()->eq('Sales.Manager_id', ':managerId'))
                ->setParameter('managerId', $managerId)
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
