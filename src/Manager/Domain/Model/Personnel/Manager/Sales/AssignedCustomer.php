<?php

namespace Manager\Domain\Model\Personnel\Manager\Sales;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Manager\Domain\Model\Personnel\Manager;
use Manager\Domain\Model\Personnel\Manager\Sales;
use Resources\Exception\RegularException;
use SharedContext\Domain\Enum\CustomerAssignmentStatus;

#[Entity]
class AssignedCustomer
{

    #[ManyToOne(targetEntity: Sales::class)]
    #[JoinColumn(name: "Sales_id", referencedColumnName: "id")]
    protected Sales $sales;

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "string", enumType: CustomerAssignmentStatus::class)]
    protected CustomerAssignmentStatus $status;

    protected function __construct()
    {
        
    }

    //
    public function closeAssignment(): void
    {
        if ($this->status !== CustomerAssignmentStatus::ACTIVE) {
            throw RegularException::forbidden('assignment already concluded');
        }
        $this->status = CustomerAssignmentStatus::CLOSED;
    }

    //
    public function isManageableByManager(Manager $manager): bool
    {
        return $this->sales->isManageableByManager($manager);
    }
}
