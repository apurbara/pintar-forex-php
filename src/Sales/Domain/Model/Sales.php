<?php

namespace Sales\Domain\Model;

use Company\Domain\Model\Personnel as PersonnelInCompanyBC;
use Company\Domain\Model\Personnel\Manager as ManagerInCompanyBC;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Resources\Exception\RegularException;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObjectList;
use Sales\Domain\DependencyModel\AreaStructure\Area;
use Sales\Domain\DependencyModel\AreaStructure\Area\CustomerData;
use Sales\Domain\DependencyModel\CustomerJourney;
use Sales\Domain\DependencyModel\Personnel;
use Sales\Domain\Model\Sales\CustomerAssignment;
use Sales\Domain\Service\SalesActivitySchedulerService;
use Sales\Domain\Task\BySales\SalesTask;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesRepository;
use SharedContext\Domain\Enum\CustomerAssignmentStatus;
use SharedContext\Domain\Enum\SalesType;

#[Entity(repositoryClass: DoctrineSalesRepository::class)]
class Sales
{

    #[FetchableObject(targetEntity: PersonnelInCompanyBC::class, joinColumnName: "Personnel_id")]
    #[ManyToOne(targetEntity: Personnel::class)]
    #[JoinColumn(name: "Personnel_id", referencedColumnName: "id")]
    protected Personnel $personnel;


    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $disabled;

    #[Column(type: "string", enumType: SalesType::class)]
    protected SalesType $type;
    
    #[FetchableObjectList(targetEntity: CustomerAssignment::class, joinColumnName: "Sales_id", paginationRequired: true)]
    #[OneToMany(targetEntity: CustomerAssignment::class, mappedBy: "sales", fetch: 'EXTRA_LAZY')]
    protected Collection $customerAssignments;
    
    //
    #[FetchableObject(targetEntity: ManagerInCompanyBC::class, joinColumnName: "Manager_id")]
    #[JoinColumn(name: "Manager_id", referencedColumnName: "id")]
    protected $manager;

    public function getId(): string
    {
        return $this->id;
    }

    protected function __construct()
    {
        
    }
    
    //
    public function assertActive(): void
    {
        if ($this->disabled) {
            throw RegularException::forbidden('inactive sales');
        }
    }
    
    //
    public function executeTask(SalesTask $task, $payload): void
    {
        $this->personnel->assertActive();
        if ($this->disabled) {
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
