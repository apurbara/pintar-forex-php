<?php

namespace Manager\Domain\Model\Personnel\Manager\Sales;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Manager\Domain\Model\Personnel\Manager;
use Manager\Domain\Model\Personnel\Manager\Sales;
use Manager\Infrastructure\Persistence\Doctrine\Repository\DoctrineAssignedCustomerRepository;
use Resources\Exception\RegularException;
use SharedContext\Domain\Enum\CustomerAssignmentStatus;

#[Entity(repositoryClass: DoctrineAssignedCustomerRepository::class)]
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
    protected function assertActiveAssignment()
    {
        if ($this->status !== CustomerAssignmentStatus::ACTIVE) {
            throw RegularException::forbidden('assignment already concluded');
        }
    }
    public function closeAssignment(): void
    {
        $this->assertActiveAssignment();
        $this->status = CustomerAssignmentStatus::GOOD_FUND;
    }
    
    public function recycleAssignment(): void
    {
        $this->assertActiveAssignment();
        $this->status = CustomerAssignmentStatus::RECYCLED;
    }

    //
    public function isManageableByManager(Manager $manager): bool
    {
        return $this->sales->isManageableByManager($manager);
    }
}
