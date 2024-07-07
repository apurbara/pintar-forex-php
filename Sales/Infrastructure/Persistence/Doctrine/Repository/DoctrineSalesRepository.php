<?php

namespace Sales\Infrastructure\Persistence\Doctrine\Repository;

use Resources\Exception\RegularException;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Sales\Domain\Model\Sales;
use Sales\Domain\Task\SalesRepository;

class DoctrineSalesRepository extends DoctrineEntityRepository implements SalesRepository
{
    
    public function ofEmail(string $email): Sales
    {
        $sales = $this->findOneBy([
            "accountInfo.email" => $email,
        ]);
        if (empty($sales)) {
            throw RegularException::unauthorized('inactive account or invalid email and password');
        }
        return $sales;
    }

    public function ofId(string $id): Sales
    {
        return $this->findOneByIdOrDie($id);
    }
}
