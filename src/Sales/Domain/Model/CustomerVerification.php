<?php

namespace Sales\Domain\Model;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Resources\Exception\RegularException;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineCustomerVerificationRepository;

#[Entity(repositoryClass: DoctrineCustomerVerificationRepository::class)]
class CustomerVerification
{

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $disabled;

    protected function __construct()
    {
        
    }

    //
    public function assertActive(): void
    {
        if ($this->disabled) {
            throw RegularException::forbidden('inactive customer verification');
        }
    }
}
