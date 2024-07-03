<?php

namespace Company\Domain\Model;

use Company\Domain\Task\TaskInCompany;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineAdminRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Resources\Exception\RegularException;
use SharedContext\Domain\ValueObject\AccountInfo;

#[Entity(repositoryClass: DoctrineAdminRepository::class)]
#[UniqueConstraint(name: "admin_mail_idx", columns: ["email"])]
class Admin implements CompanyUser
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
    private function executeAdminTaskInCompany(AdminTaskInCompany $task, $payload): void
    {
        $task->executeInCompany($payload);
    }
    public function executeTaskInCompany(TaskInCompany $task, $payload): void
    {
        if ($this->disabled) {
            throw RegularException::unauthorized('only active admin can make this request');
        }
        $this->executeAdminTaskInCompany($task, $payload);
    }
}
