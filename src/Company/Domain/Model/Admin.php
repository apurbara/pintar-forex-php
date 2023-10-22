<?php

namespace Company\Domain\Model;

use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineAdminRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\UniqueConstraint;
use SharedContext\Domain\ValueObject\AccountInfo;

#[Entity(repositoryClass: DoctrineAdminRepository::class)]
#[UniqueConstraint(name: "admin_mail_idx", columns: ["email"])]
class Admin
{

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $disabled;

    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected DateTimeImmutable $createdTime;

    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $aSuperUser;

    #[Embedded(class: AccountInfo::class, columnPrefix: false)]
    protected AccountInfo $accountInfo;
    
    public function __construct(AdminData $data)
    {
        $this->id = $data->id;
        $this->disabled = false;
        $this->createdTime = new \DateTimeImmutable();
        $this->aSuperUser = $data->aSuperUser;
        $this->accountInfo = new AccountInfo($data->accountInfoData);
    }

    //
    public function executeTaskInCompany(AdminTaskInCompany $task, $payload): void
    {
        if ($this->disabled) {
            throw \Resources\Exception\RegularException::unauthorized('only active admin can make this request');
        }
        $task->executeInCompany($payload);
    }
}
