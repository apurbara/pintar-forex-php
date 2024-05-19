<?php

namespace Company\Domain\Model;

use Company\Domain\Model\Personnel\Manager;
use Company\Domain\Model\Personnel\Manager\Sales;
use Company\Domain\Model\Personnel\ManagerData;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrinePersonnelRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
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
    protected bool $suspended;

    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected DateTimeImmutable $createdTime;

    #[Embedded(class: AccountInfo::class, columnPrefix: false)]
    protected AccountInfo $accountInfo;
    
    //
    
    #[FetchableObjectList(targetEntity: Sales::class, joinColumnName: "Personnel_id", paginationRequired: false)]
    protected $salesAssignments;
    #[FetchableObjectList(targetEntity: Manager::class, joinColumnName: "Personnel_id", paginationRequired: false)]
    #[OneToMany(targetEntity: Manager::class, mappedBy: "personnel", fetch: "EXTRA_LAZY")]
    protected Collection $managerAssignments;

    public function __construct(PersonnelData $data)
    {
        $this->id = $data->id;
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
    public function assertActive(): void
    {
        if ($this->suspended) {
            throw RegularException::forbidden('inactive personnel');
        }
    }
    protected function assertHavingActiveManagerAssignment(): void
    {
        $criteria = Criteria::create()
                ->andWhere(Criteria::expr()->eq('disabled', false));
        if ($this->managerAssignments->matching($criteria)->isEmpty()) {
            throw RegularException::forbidden('only active personnel having manager assignment can  make this request');
        }
    }

    //
    public function executeTaskInCompany(PersonnelTaskInCompany $task, $payload): void
    {
        if ($this->suspended) {
            throw RegularException::forbidden('only active personnel can  make this request');
        }
        if ($task instanceof PersonnelHavingManagerAssignmentTaskInCompany) {
            $this->assertHavingActiveManagerAssignment();
        }
        $task->executeInCompany($payload);
    }

    //
    public function assignAsManager(ManagerData $managerData): Manager
    {
        if ($this->suspended) {
            throw RegularException::forbidden('only active personnel allow to be assigned as manager');
        }
        return new Manager($this, $managerData);
    }
}
