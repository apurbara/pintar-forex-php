<?php

namespace Manager\Domain\Model\Personnel\Manager;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Manager\Domain\Model\Personnel\Manager;

#[Entity]
class Sales
{

    #[ManyToOne(targetEntity: Manager::class)]
    #[JoinColumn(name: "Manager_id", referencedColumnName: "id")]
    protected Manager $manager;

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $disabled;

    protected function __construct()
    {
        
    }

    //
    public function isManageableByManager(Manager $manager): bool
    {
        return $this->manager === $manager;
    }
}
