<?php

namespace Sales\Domain\Model;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;

#[Entity]
class Personnel
{

    #[Id, Column(type: "guid")]
    protected string $id;
    
    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $disabled;
    
    protected function __construct()
    {
    }
}
