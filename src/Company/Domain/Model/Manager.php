<?php

namespace Company\Domain\Model;

use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineManagerRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Resources\Exception\RegularException;
use SharedContext\Domain\ValueObject\AccountInfo;

#[Entity(repositoryClass: DoctrineManagerRepository::class)]
#[UniqueConstraint(name: "manager_mail_idx", columns: ["email"])]
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
    
    public function __construct(string $id, ManagerData $data)
    {
        $this->id = $id;
        $this->suspended = false;
        $this->createdTime = new \DateTimeImmutable();
        $this->accountInfo = new AccountInfo($data->accountInfoData);
    }
    
    public function suspend(): void
    {
        $this->suspended = true;
    }
    
    public function unsuspend(): void
    {
        $this->suspended = false;
    }

    //
    public function executeTaskInCompany(ManagerTaskInCompany $task, $payload): void
    {
        if ($this->suspended) {
            throw RegularException::forbidden('only active manager can  make this request');
        }
        $task->executeInCompany($payload);
    }
}
