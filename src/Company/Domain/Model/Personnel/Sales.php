<?php

namespace Company\Domain\Model\Personnel;

use Company\Domain\Model\AreaStructure\Area;
use Company\Domain\Model\AreaStructure\Area\Customer;
use Company\Domain\Model\CustomerJourney;
use Company\Domain\Model\Personnel;
use Company\Domain\Model\Personnel\Sales\CustomerAssignment;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Resources\Event\ContainEventsInterface;
use Resources\Event\ContainEventsTrait;
use Resources\Exception\RegularException;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObjectList;
use SharedContext\Domain\Enum\CustomerAssignmentStatus;
use SharedContext\Domain\Enum\SalesType;
use SharedContext\Domain\Event\MultipleCustomerAssignmentReceivedBySales;

#[Entity(repositoryClass: DoctrineSalesRepository::class)]
class Sales implements ContainEventsInterface
{

    use ContainEventsTrait;

    #[FetchableObject(targetEntity: Personnel::class, joinColumnName: "Personnel_id")]
    #[ManyToOne(targetEntity: Personnel::class)]
    #[JoinColumn(name: "Personnel_id", referencedColumnName: "id")]
    protected Personnel $personnel;

    #[FetchableObject(targetEntity: Area::class, joinColumnName: "Area_id")]
    #[ManyToOne(targetEntity: Area::class)]
    #[JoinColumn(name: "Area_id", referencedColumnName: "id")]
    protected ?Area $area;

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected DateTimeImmutable $createdTime;

    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected ?DateTimeImmutable $cancelTime;

    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $cancelled;

    #[Column(type: "string", enumType: SalesType::class)]
    protected SalesType $type;

    #[FetchableObjectList(targetEntity: CustomerAssignment::class, joinColumnName: "Sales_id", paginationRequired: true)]
    #[OneToMany(targetEntity: CustomerAssignment::class, mappedBy: "sales", fetch: "EXTRA_LAZY")]
    protected Collection $customerAssignments;

    public function __construct(Personnel $personnel, ?Area $area, string $id, SalesData $data)
    {
        $this->personnel = $personnel;
        $this->area = $area;
        $this->id = $id;
        $this->createdTime = new DateTimeImmutable();
        $this->cancelTime = null;
        $this->cancelled = false;
        $this->type = SalesType::from($data->type);
        //
        $this->personnel->assertActive();
        $this->area?->assertActive();
    }

    public function cancel(): void
    {
        $this->cancelled = true;
        $this->cancelTime = new DateTimeImmutable();

        $criteria = Criteria::create()
                ->andWhere(Criteria::expr()->eq('status', CustomerAssignmentStatus::ACTIVE));
        foreach ($this->customerAssignments->matching($criteria)->getIterator() as $customerAssignment) {
            $customerAssignment->cancel();
        }
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
