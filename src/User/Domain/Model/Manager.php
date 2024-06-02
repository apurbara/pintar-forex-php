<?php

namespace User\Domain\Model;

use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Resources\Exception\RegularException;
use SharedContext\Domain\ValueObject\AccountInfo;
use SharedContext\Domain\ValueObject\ChangeUserPasswordData;
use User\Domain\Task\ByManager\ManagerTask;
use User\Infrastructure\Persistence\Doctrine\Repository\DoctrineManagerRepository;

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

    public function changeName(string $name): void
    {
        $this->accountInfo = $this->accountInfo->changeName($name);
    }
    public function changePassword(ChangeUserPasswordData $changeUserPasswordData): void
    {
        $this->accountInfo = $this->accountInfo->changePassword($changeUserPasswordData);
    }

    //
    public function login(string $password): string
    {
        if ($this->suspended || !$this->accountInfo->passwordMatch($password)) {
            throw RegularException::unauthorized('inactive account or invalid email and password');
        }
        return $this->id;
    }

    //
    public function executeTask(ManagerTask $task, $payload): void
    {
        if ($this->suspended) {
            throw RegularException::forbidden('only active personnel can make this request');
        }
        $task->executeByManager($this, $payload);
    }
}
