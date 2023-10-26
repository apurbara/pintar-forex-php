<?php

namespace Sales\Domain\Model;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Resources\Exception\RegularException;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesActivityRepository;

#[Entity(repositoryClass: DoctrineSalesActivityRepository::class)]
class SalesActivity
{

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $disabled;

    #[Column(type: "smallint", nullable: false)]
    protected int $duration;

    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $initial;

    protected function __construct()
    {
        
    }

    //
    public function assertActive(): void
    {
        if ($this->disabled) {
            throw RegularException::forbidden('inactive sales activity');
        }
    }
}
