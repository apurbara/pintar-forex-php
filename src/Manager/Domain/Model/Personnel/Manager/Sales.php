<?php

namespace Manager\Domain\Model\Personnel\Manager;

use Company\Domain\Model\AreaStructure\Area as AreaInCompanyBC;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Manager\Domain\Model\AreaStructure\Area;
use Manager\Domain\Model\AreaStructure\Area\Customer;
use Manager\Domain\Model\CustomerJourney;
use Manager\Domain\Model\Personnel\Manager;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer;
use Resources\Exception\RegularException;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObjectList;
use SharedContext\Domain\Enum\CustomerAssignmentStatus;
use SharedContext\Domain\Enum\SalesType;

#[Entity]
class Sales
{

    #[FetchableObject(targetEntity: Manager::class, joinColumnName: "Manager_id")]
    #[ManyToOne(targetEntity: Manager::class, inversedBy: "salesList", fetch: "LAZY")]
    #[JoinColumn(name: "Manager_id", referencedColumnName: "id")]
    protected Manager $manager;

    #[FetchableObject(targetEntity: AreaInCompanyBC::class, joinColumnName: "Area_id")]
    #[ManyToOne(targetEntity: Area::class)]
    #[JoinColumn(name: "Area_id", referencedColumnName: "id")]
    protected Area $area;

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $disabled;

    #[Column(type: "string", enumType: SalesType::class)]
    protected SalesType $type;
    
    #[FetchableObjectList(targetEntity: AssignedCustomer::class, joinColumnName: "Sales_id", paginationRequired: true)]
    #[OneToMany(targetEntity: AssignedCustomer::class, mappedBy: "sales", fetch: "EXTRA_LAZY")]
    protected Collection $assignedCustomers;

    public function getType(): SalesType
    {
        return $this->type;
    }

    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    protected function __construct()
    {
        
    }

    //
    public function isManageableByManager(Manager $manager): bool
    {
        return $this->manager === $manager;
    }

    //
    public function receiveCustomerAssignment(
            string $assignedCustomerId, Customer $customer, CustomerJourney $customerJourney): AssignedCustomer
    {
        if ($this->disabled) {
            throw RegularException::forbidden('inactive sales');
        }
        return new AssignedCustomer($this, $customer, $customerJourney, $assignedCustomerId);
    }

    public function countAssignmentPriorityWithCustomer(Customer $customer): float|int
    {
        if (!$customer->areaEquals($this->area)) {
            return INF;
        }
        $criteria = Criteria::create()
                ->andWhere(Criteria::expr()->eq('status', CustomerAssignmentStatus::ACTIVE));
        return $this->assignedCustomers->matching($criteria)->count();
    }
}
