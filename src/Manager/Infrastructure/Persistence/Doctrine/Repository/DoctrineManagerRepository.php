<?php

namespace Manager\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\ORM\NoResultException;
use Manager\Application\Service\Manager\ManagerRepository;
use Manager\Domain\Model\Personnel\Manager;
use Resources\Exception\RegularException;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;

class DoctrineManagerRepository extends DoctrineEntityRepository implements ManagerRepository
{

    public function aManagerBelongsToPersonnel(string $personnelId, string $managerId): Manager
    {
        $params = [
            'personnelId' => $personnelId,
            'managerId' => $managerId
        ];
        $qb = $this->createQueryBuilder('manager');
        $qb->select('manager')
                ->innerJoin('manager.personnel', 'personnel')
                ->andWhere($qb->expr()->eq('personnel.id', ':personnelId'))
                ->andWhere($qb->expr()->eq('manager.id', ':managerId'))
                ->setMaxResults(1);

        try {
            return $qb->getQuery()->getSingleResult();
        } catch (NoResultException $ex) {
            throw RegularException::notFound('manager not found');
        }
    }
}
