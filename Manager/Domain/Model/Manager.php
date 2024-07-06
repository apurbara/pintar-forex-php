<?php

namespace Manager\Domain\Model;

use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Manager\Domain\Task\ManagerTask;
use Manager\Infrastructure\Persistence\Doctrine\Repository\DoctrineManagerRepository;
use Resources\Exception\RegularException;
use Shared\Domain\ValueObject\AccountInfo;

#[Entity(repositoryClass: DoctrineManagerRepository::class)]
class Manager
{

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $suspended;

    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected DateTimeImmutable $createdTime;

    #[Embedded(class: AccountInfo::class, columnPrefix: false)]
    protected AccountInfo $accountInfo;

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
        if ($this->suspended) {
            throw RegularException::forbidden('only active manager can make this request');
        }
        $task->executeByManager($this, $payload);
    }
}
