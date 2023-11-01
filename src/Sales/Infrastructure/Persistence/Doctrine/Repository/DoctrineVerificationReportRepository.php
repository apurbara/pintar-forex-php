<?php

namespace Sales\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\DBAL\Query\QueryBuilder;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrinePaginationListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;
use Sales\Domain\Task\VerificationReport\VerificationReportRepository;

class DoctrineVerificationReportRepository extends DoctrineEntityRepository implements VerificationReportRepository
{

    protected function createCoreQueryBuilder(): QueryBuilder
    {
        return parent::createCoreQueryBuilder()
                ->innerJoin('VerificationReport', 'Customer', 'Customer', 'VerificationReport.Customer_id = Customer.id')
                ->innerJoin('Customer', 'AssignedCustomer', 'AssignedCustomer',
                'AssignedCustomer.Customer_id = Customer.id');
    }

    public function aVerificationReportOfCustomerAssgnedToSales(string $salesId, string $id): array
    {
        $filters = [
            new Filter($salesId, 'AssignedCustomer.Sales_id'),
            new Filter($id, 'VerificationReport.id'),
        ];
        return $this->fetchOneOrDie($filters);
    }

    public function verificationReportListOfCustomerAssgnedToSales(string $salesId, array $paginationSchema): array
    {
        $doctrinePaginationListCategory = DoctrinePaginationListCategory::fromSchema($paginationSchema)
                ->addFilter(new Filter($salesId, 'AssignedCustomer.Sales_id'));
        return $this->fetchPaginationList($doctrinePaginationListCategory);
    }

    //
    public function aVerificationReportOnAssignedCustomerAssociateWithCustomerVerificationId(
            string $assignedCustomerId, string $customerVerificationId): array
    {
        $filters = [
            new Filter($assignedCustomerId, 'AssignedCustomer.id'),
            new Filter($customerVerificationId, 'VerificationReport.CustomerVerification_id'),
        ];
        return $this->fetchOneOrDie($filters);
    }
}
