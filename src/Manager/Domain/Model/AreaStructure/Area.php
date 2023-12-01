<?php

namespace Manager\Domain\Model\AreaStructure;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;

#[Entity]
class Area
{

    #[Id, Column(type: "guid")]
    protected string $id;

    protected function __construct()
    {
        
    }
}
