<?php

namespace Manager\Domain\Model\AreaStructure\Area;

use Company\Domain\Model\AreaStructure\Area as AreaInCompanyBC;
use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Manager\Domain\Model\AreaStructure\Area;
use Manager\Domain\Model\AreaStructure\Area\Customer\VerificationReport;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer;
use Manager\Infrastructure\Persistence\Doctrine\Repository\DoctrineCustomerRepository;
use Resources\Exception\RegularException;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObjectList;
use SharedContext\Domain\Enum\CustomerAssignmentStatus;

#[Entity(repositoryClass: DoctrineCustomerRepository::class)]
class Customer
{

    #[FetchableObject(targetEntity: AreaInCompanyBC::class, joinColumnName: "Area_id")]
    #[ManyToOne(targetEntity: Area::class)]
    #[JoinColumn(name: "Area_id", referencedColumnName: "id")]
    protected Area $area;

    #[Id, Column(type: "guid")]
    protected string $id;

    //query purpose
    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $disabled;

    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected DateTimeImmutable $createdTime;

    #[Column(type: "string", length: 255, nullable: false)]
    protected string $name;

    #[Column(type: "string", length: 255, nullable: true)]
    protected string $email;

    #[Column(type: "string", length: 255, nullable: false)]
    protected string $phone;

    #[Column(type: "string", length: 255, nullable: true)]
    protected string $source;

    #[FetchableObjectList(targetEntity: AssignedCustomer::class, joinColumnName: "Customer_id",
                paginationRequired: false)]
    #[OneToMany(targetEntity: AssignedCustomer::class, mappedBy: "customer", cascade: ["persist"], fetch: "EXTRA_LAZY")]
    protected Collection $assignedCustomers;

    //
    #[FetchableObjectList(targetEntity: VerificationReport::class, joinColumnName: "Customer_id",
                paginationRequired: false)]
    protected $verificationReports;

    public function getId(): string
    {
        return $this->id;
    }

    protected function __construct()
    {
        
    }

    //
    public function areaEquals(Area $area): bool
    {
        return $this->area === $area;
    }

    public function assertHasNoActiveAssignment(): void
    {
        $criteria = Criteria::create()
                ->andWhere(Criteria::expr()->eq('status', CustomerAssignmentStatus::ACTIVE));
        if (!$this->assignedCustomers->matching($criteria)->isEmpty()) {
            throw RegularException::forbidden('customer has active assignment');
        }
    }
}
