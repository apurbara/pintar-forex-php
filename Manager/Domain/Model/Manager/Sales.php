<?php

namespace Manager\Domain\Model\Manager;

use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Manager\Domain\DependencyModel\Customer;
use Manager\Domain\DependencyModel\CustomerJourney;
use Manager\Domain\Model\Manager;
use Manager\Domain\Model\Manager\Sales\CustomerAssignment;
use Manager\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesRepository;
use Resources\Event\ContainEventsInterface;
use Resources\Event\ContainEventsTrait;
use Resources\Exception\RegularException;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObjectList;
use SharedContext\Domain\Enum\CustomerAssignmentStatus;
use SharedContext\Domain\Enum\SalesType;
use SharedContext\Domain\Event\MultipleCustomerAssignmentReceivedBySales;
use SharedContext\Domain\ValueObject\AccountInfo;

#[Entity(repositoryClass: DoctrineSalesRepository::class)]
class Sales implements ContainEventsInterface
{

    use ContainEventsTrait;

    #[FetchableObject(targetEntity: Manager::class, joinColumnName: "Manager_id")]
    #[ManyToOne(targetEntity: Manager::class, fetch: "EXTRA_LAZY")]
    #[JoinColumn(name: "Manager_id", referencedColumnName: "id")]
    protected Manager $manager;

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $cancelled;

    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected DateTimeImmutable $createdTime;

    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected ?DateTimeImmutable $cancelTime;

    #[Embedded(class: AccountInfo::class, columnPrefix: false)]
    protected AccountInfo $accountInfo;

    #[Column(type: "string", enumType: SalesType::class)]
    protected SalesType $type;

    #[FetchableObjectList(targetEntity: CustomerAssignment::class, joinColumnName: "Sales_id", paginationRequired: true)]
    #[OneToMany(targetEntity: CustomerAssignment::class, mappedBy: "sales", fetch: "EXTRA_LAZY")]
    protected Collection $customerAssignments;

    protected function __construct()
    {
        
    }

    //
    public function isInHouseSales(): bool
    {
        return $this->type === SalesType::IN_HOUSE;
    }

    public function assertActive(): void
    {
        if ($this->cancelled) {
            throw RegularException::forbidden('inactive sales');
        }
    }

    public function belongsToManager(Manager $manager): bool
    {
        return $this->manager === $manager;
    }

    public function assertBelongsToManager(Manager $manager): void
    {
        if (!$this->belongsToManager($manager)) {
            throw RegularException::forbidden('sales does not belongs to manager');
        }
    }

    //
    protected ?int $activeCustomerAssignmentCount = null;

    public function calculateActiveCustomerAssignmentsCount(): ?int
    {
        $criteria = Criteria::create()
                ->andWhere(Criteria::expr()->eq('status', CustomerAssignmentStatus::ACTIVE));
        $this->activeCustomerAssignmentCount ??= $this->customerAssignments->matching($criteria)->count();
        return $this->activeCustomerAssignmentCount;
    }

    protected ?MultipleCustomerAssignmentReceivedBySales $multipleCustomerAssignmentReceivedBySalesEvent;

    public function receiveCustomerAssignment(string $id, Customer $customer, ?CustomerJourney $customerJourney): ?CustomerAssignment
    {

        if (empty($this->multipleCustomerAssignmentReceivedBySalesEvent)) {
            $this->multipleCustomerAssignmentReceivedBySalesEvent = new MultipleCustomerAssignmentReceivedBySales($this->id);
            $this->recordEvent($this->multipleCustomerAssignmentReceivedBySalesEvent);
        }

        try {
            $customerAssignment = new CustomerAssignment($this, $customer, $customerJourney, $id);
            $this->multipleCustomerAssignmentReceivedBySalesEvent->addCustomerAssignmentId($id);
            $this->activeCustomerAssignmentCount++;
            return $customerAssignment;
        } catch (RegularException $ex) {
            return null;
        }
    }
}
