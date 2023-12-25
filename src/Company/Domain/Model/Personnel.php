<?php

namespace Company\Domain\Model;

use Company\Domain\Model\Personnel\Manager;
use Company\Domain\Model\Personnel\Manager\Sales;
use Company\Domain\Model\Personnel\ManagerData;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrinePersonnelRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Resources\Exception\RegularException;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObjectList;
use SharedContext\Domain\ValueObject\AccountInfo;

#[Entity(repositoryClass: DoctrinePersonnelRepository::class)]
#[UniqueConstraint(name: "personnel_mail_idx", columns: ["email"])]
class Personnel
{

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $disabled;

    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected DateTimeImmutable $createdTime;

    #[Embedded(class: AccountInfo::class, columnPrefix: false)]
    protected AccountInfo $accountInfo;
    
    //
    
    #[FetchableObjectList(targetEntity: Sales::class, joinColumnName: "Personnel_id", paginationRequired: false)]
    protected $salesAssignments;
    #[FetchableObjectList(targetEntity: Manager::class, joinColumnName: "Personnel_id", paginationRequired: false)]
    protected $managerAssignments;

    public function __construct(PersonnelData $data)
    {
        $this->id = $data->id;
        $this->disabled = false;
        $this->createdTime = new \DateTimeImmutable();
        $this->accountInfo = new AccountInfo($data->accountInfoData);
    }

    //
    public function assertActive(): void
    {
        if ($this->disabled) {
            throw RegularException::forbidden('inactive personnel');
        }
    }

    //
    public function executeTaskInCompany(PersonnelTaskInCompany $task, $payload): void
    {
        if ($this->disabled) {
            throw RegularException::forbidden('only active personnel can  make this request');
        }
        $task->executeInCompany($payload);
    }

    //
    public function assignAsManager(ManagerData $managerData): Manager
    {
        if ($this->disabled) {
            throw RegularException::forbidden('only active personnel allow to be assigned as manager');
        }
        return new Manager($this, $managerData);
    }
}
