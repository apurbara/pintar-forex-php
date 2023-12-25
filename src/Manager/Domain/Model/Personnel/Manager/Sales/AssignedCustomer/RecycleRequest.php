<?php

namespace Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer;

use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Manager\Domain\Model\Personnel\Manager;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer;
use Manager\Infrastructure\Persistence\Doctrine\Repository\DoctrineRecycleRequestRepository;
use Resources\Event\ContainEventsInterface;
use Resources\Event\ContainEventsTrait;
use Resources\Exception\RegularException;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;
use SharedContext\Domain\Enum\ManagementApprovalStatus;

#[Entity(repositoryClass: DoctrineRecycleRequestRepository::class)]
class RecycleRequest implements ContainEventsInterface
{

    use ContainEventsTrait;

    #[FetchableObject(targetEntity: AssignedCustomer::class, joinColumnName: "AssignedCustomer_id")]
    #[ManyToOne(targetEntity: AssignedCustomer::class)]
    #[JoinColumn(name: "AssignedCustomer_id", referencedColumnName: "id")]
    protected AssignedCustomer $assignedCustomer;

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected DateTimeImmutable $createdTime;

    #[Column(type: "string", enumType: ManagementApprovalStatus::class)]
    protected ManagementApprovalStatus $status;

    #[Column(type: "text", nullable: true)]
    protected ?string $note;

    protected function __construct()
    {
        
    }

    //
    protected function assertWaitingForApproval()
    {
        if ($this->status != ManagementApprovalStatus::WAITING_FOR_APPROVAL) {
            throw RegularException::forbidden('unable to process concluded request');
        }
    }

    public function approve(): void
    {
        $this->assertWaitingForApproval();
        $this->status = ManagementApprovalStatus::APPROVED;
        $this->assignedCustomer->recycleAssignment();
        $this->storeChildContainEvents($this->assignedCustomer);
    }

    public function reject(): void
    {
        $this->assertWaitingForApproval();
        $this->status = ManagementApprovalStatus::REJECTED;
    }

    //
    public function assertManageableByManager(Manager $manager): void
    {
        if (!$this->assignedCustomer->isManageableByManager($manager)) {
            throw RegularException::forbidden('unmanaged recycle request');
        }
    }
    
}
