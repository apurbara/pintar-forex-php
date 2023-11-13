<?php

namespace Manager\Domain\Model\Personnel;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Manager\Domain\Model\Personnel;
use Manager\Domain\Task\ManagerTask;
use Manager\Infrastructure\Persistence\Doctrine\Repository\DoctrineManagerRepository;
use Resources\Exception\RegularException;

#[Entity(repositoryClass: DoctrineManagerRepository::class)]
class Manager
{

    #[ManyToOne(targetEntity: Personnel::class)]
    #[JoinColumn(name: "Personnel_id", referencedColumnName: "id")]
    protected Personnel $personnel;

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $disabled;

    public function getId(): string
    {
        return $this->id;
    }

    protected function __construct()
    {
        
    }

    //
    public function executeTask(ManagerTask $task, $payload): void
    {
        if ($this->disabled) {
            throw RegularException::forbidden('only active manager can make this request');
        }
        $task->executeByManager($this, $payload);
    }

}
