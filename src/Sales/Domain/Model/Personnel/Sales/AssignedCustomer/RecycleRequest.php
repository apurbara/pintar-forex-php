<?php

namespace Sales\Domain\Model\Personnel\Sales\AssignedCustomer;

use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Resources\Exception\RegularException;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;
use Sales\Domain\Model\Personnel\Sales;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineRecycleRequestRepository;
use SharedContext\Domain\Enum\ManagementApprovalStatus;

#[Entity(repositoryClass: DoctrineRecycleRequestRepository::class)]
class RecycleRequest
{

    #[FetchableObject(targetEntity: AssignedCustomer::class, joinColumnName: "AssignedCustomer_id")]
    #[ManyToOne(targetEntity: AssignedCustomer::class, inversedBy: "recycleReports", fetch: "LAZY")]
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

    public function __construct(AssignedCustomer $assignedCustomer, RecycleRequestData $data)
    {
        $this->assignedCustomer = $assignedCustomer;
        $this->id = $data->id;
        $this->createdTime = new \DateTimeImmutable();
        $this->status = ManagementApprovalStatus::WAITING_FOR_APPROVAL;
        $this->note = $data->note;
    }

    public function update(RecycleRequestData $data): void
    {
        if ($this->status !== ManagementApprovalStatus::WAITING_FOR_APPROVAL) {
            throw RegularException::forbidden('request already concluded');
        }
        $this->note = $data->note;
    }

    //
    public function assertManageableBySales(Sales $sales): void
    {
        $this->assignedCustomer->assertBelongsToSales($sales);
    }
    
    //
    public function isOngoing(): bool
    {
        return $this->status === ManagementApprovalStatus::WAITING_FOR_APPROVAL;
    }
}
