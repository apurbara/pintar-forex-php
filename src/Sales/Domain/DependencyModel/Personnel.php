<?php

namespace Sales\Domain\DependencyModel;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Resources\Exception\RegularException;

#[Entity]
class Personnel
{

    #[Id, Column(type: "guid")]
    protected string $id;
    
    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $suspended;
    
    protected function __construct()
    {
    }
    
    //
    public function assertActive(): void
    {
        if ($this->suspended) {
            throw RegularException::forbidden('inactive personnel');
        }
    }
}
