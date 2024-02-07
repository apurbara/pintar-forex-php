<?php

namespace Sales\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\ORM\NoResultException;
use Resources\Exception\RegularException;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Sales\Application\Service\Sales\SalesRepository;
use Sales\Domain\Model\Personnel\Sales;

class DoctrineSalesRepository extends DoctrineEntityRepository implements SalesRepository
{

    public function aSalesBelongToPersonnel(string $personnelId, string $salesId): Sales
    {
        $qb = $this->createQueryBuilder('sales');
        $qb->select('sales')
                ->innerJoin('sales.personnel', 'personnel')
                ->andWhere($qb->expr()->eq('sales.id', ':salesId'))
                ->setParameter('salesId', $salesId)
                ->andWhere($qb->expr()->eq('personnel.id', ':personnelId'))
                ->setParameter('personnelId', $personnelId)
                ->setMaxResults(1);

        try {
            return $qb->getQuery()->getSingleResult();
        } catch (NoResultException $ex) {
            throw RegularException::notFound('sales not found');
        }
    }

    public function ofId(string $id): Sales
    {
        return $this->findOneByIdOrDie($id);
    }
}
