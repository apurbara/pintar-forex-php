<?php

namespace Sales\Domain\Model;

use Company\Domain\Model\Manager as FetchableManagerFromCompanyBC;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToMany;
use Resources\Exception\RegularException;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObjectList;
use Sales\Domain\Model\Sales\CustomerAssignment;
use Sales\Domain\Service\SalesActivitySchedulerService;
use Sales\Domain\Task\SalesTask;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesRepository;
use Shared\Domain\Enum\CustomerAssignmentStatus;
use Shared\Domain\Enum\SalesType;
use Shared\Domain\ValueObject\AccountInfo;
use Shared\Domain\ValueObject\ChangeUserPasswordData;

#[Entity(repositoryClass: DoctrineSalesRepository::class)]
class Sales
{

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $contractTerminated;

    #[Column(type: "string", enumType: SalesType::class)]
    protected SalesType $type;
    
    #[Embedded(class: AccountInfo::class, columnPrefix: false)]
    protected AccountInfo $accountInfo;
    
    #[FetchableObjectList(targetEntity: CustomerAssignment::class, joinColumnName: "Sales_id", paginationRequired: true)]
    #[OneToMany(targetEntity: CustomerAssignment::class, mappedBy: "sales", fetch: 'EXTRA_LAZY')]
    protected Collection $customerAssignments;
    
    //
    #[FetchableObject(targetEntity: FetchableManagerFromCompanyBC::class, joinColumnName: "Manager_id")]
    #[JoinColumn(name: "Manager_id", referencedColumnName: "id")]
    protected $manager;

    public function getId(): string
    {
        return $this->id;
    }

    protected function __construct()
    {
        
    }
    
    public function changePassword(ChangeUserPasswordData $changePasswordData): void
    {
        $this->accountInfo = $this->accountInfo->changePassword($changePasswordData);
    }

    public function changeName(string $name): void
    {
        $this->accountInfo = $this->accountInfo->changeName($name);
    }

    public function login(string $password): string
    {
        if ($this->contractTerminated || !$this->accountInfo->passwordMatch($password)) {
            throw RegularException::unauthorized('inactive account or invalid email and password');
        }
        return $this->id;
    }
    
    //
    public function assertActive(): void
    {
        if ($this->contractTerminated) {
            throw RegularException::forbidden('inactive sales');
        }
    }
    
    //
    public function executeTask(SalesTask $task, $payload): void
    {
        if ($this->contractTerminated) {
            throw RegularException::forbidden('only active sales can make this request');
        }
        $task->executeBySales($this, $payload);
    }

    //
    public function registerAllUpcomingScheduleToScheduler(SalesActivitySchedulerService $service): void
    {
        $criteria = Criteria::create()
                ->andWhere(Criteria::expr()->eq('status', CustomerAssignmentStatus::ACTIVE));
        foreach ($this->customerAssignments->matching($criteria)->getIterator() as $customerAssignment) {
            $customerAssignment->addUpcomingScheduleToSchedulerService($service);
        }
    }
}
