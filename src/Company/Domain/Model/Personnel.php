<?php

namespace Company\Domain\Model;

use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrinePersonnelRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\UniqueConstraint;
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

    public function __construct(PersonnelData $data)
    {
        $this->id = $data->id;
        $this->disabled = false;
        $this->createdTime = new \DateTimeImmutable();
        $this->accountInfo = new AccountInfo($data->accountInfoData);
    }
}
