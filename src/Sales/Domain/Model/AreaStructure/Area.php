<?php

namespace Sales\Domain\Model\AreaStructure;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Resources\Exception\RegularException;
use Sales\Domain\Model\AreaStructure\Area\Customer;
use Sales\Domain\Model\AreaStructure\Area\CustomerData;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineAreaRepository;

#[Entity(repositoryClass: DoctrineAreaRepository::class)]
class Area
{

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $disabled;

    protected function __construct()
    {
        
    }

    //
    public function assertAccessible(): void
    {
        if ($this->disabled) {
            throw RegularException::forbidden('inaccessible area');
        }
    }

    //
    public function createCustomer(CustomerData $customerData): Customer
    {
        return new Customer($this, $customerData);
    }
}
