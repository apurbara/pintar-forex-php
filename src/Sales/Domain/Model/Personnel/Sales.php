<?php

namespace Sales\Domain\Model\Personnel;

use Company\Domain\Model\Personnel as PersonnelInCompanyBC;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Resources\Attributes\FetchableEntity;
use Resources\Exception\RegularException;
use Sales\Domain\Model\AreaStructure\Area;
use Sales\Domain\Model\AreaStructure\Area\CustomerData;
use Sales\Domain\Model\CustomerJourney;
use Sales\Domain\Model\Personnel;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;
use Sales\Domain\Service\SalesActivitySchedulerService;
use Sales\Domain\Task\SalesTask;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesRepository;
use SharedContext\Domain\Enum\CustomerAssignmentStatus;
use SharedContext\Domain\Enum\SalesType;

#[Entity(repositoryClass: DoctrineSalesRepository::class)]
class Sales
{

    #[FetchableEntity(targetEntity: PersonnelInCompanyBC::class, joinColumnName: "Personnel_id")]
    #[ManyToOne(targetEntity: Personnel::class)]
    #[JoinColumn(name: "Personnel_id", referencedColumnName: "id")]
    protected Personnel $personnel;

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $disabled;

    #[Column(type: "string", enumType: SalesType::class)]
    protected SalesType $type;
    
    #[OneToMany(targetEntity: AssignedCustomer::class, mappedBy: "sales", fetch: 'EXTRA_LAZY')]
    protected Collection $assignedCustomers;

    public function getId(): string
    {
        return $this->id;
    }

    protected function __construct()
    {
        
    }

    //
    public function executeTask(SalesTask $task, $payload): void
    {
        if ($this->disabled) {
            throw RegularException::forbidden('only active sales can make this request');
        }
        $task->executeBySales($this, $payload);
    }

    //
    public function registerNewCustomer(
            Area $customerArea, ?CustomerJourney $initialCustomerJourney, string $assignedCustomerId,
            CustomerData $customerData): AssignedCustomer
    {
        $customer = $customerArea->createCustomer($customerData);
        return new AssignedCustomer($this, $customer, $initialCustomerJourney, $assignedCustomerId);
    }
    
    //
    public function registerAllUpcomingScheduleToScheduler(SalesActivitySchedulerService $service): void
    {
        $criteria = Criteria::create()
                ->andWhere(Criteria::expr()->eq('status', CustomerAssignmentStatus::ACTIVE));
        foreach ($this->assignedCustomers->matching($criteria)->getIterator() as $assignedCustomer) {
            $assignedCustomer->addUpcomingScheduleToSchedulerService($service);
        }
    }
}
