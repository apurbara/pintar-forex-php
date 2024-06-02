<?php

namespace User\Infrastructure\Persistence\Doctrine\Repository;

use Resources\Exception\RegularException;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use User\Application\Service\Guest\SalesRepository as SalesRepository2;
use User\Application\Service\Sales\SalesRepository;
use User\Domain\Model\Sales;

class DoctrineSalesRepository extends DoctrineEntityRepository implements SalesRepository2, SalesRepository
{

    public function activeSalesByEmail(string $email): Sales
    {
        $result = $this->findOneBy([
            'accountInfo.email' => $email,
            'cancelled' => false,
        ]);
        if (empty($result)) {
            throw RegularException::notFound('account not found');
        }
        return $result;
    }

    public function ofId(string $id): Sales
    {
        return $this->findOneByIdOrDie($id);
    }
}
