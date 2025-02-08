<?php

namespace Manager\Domain\Model\Manager\Sales\FactFindingAssignment;

use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Manager\Domain\Model\Manager;
use Manager\Domain\Model\Manager\Sales\FactFindingAssignment;
use Manager\Infrastructure\Persistence\Doctrine\Repository\DoctrineClosingRequestByFactFinderRepository;
use Resources\Exception\RegularException;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;
use Shared\Domain\Enum\ManagementApprovalStatus;

#[Entity(repositoryClass: DoctrineClosingRequestByFactFinderRepository::class)]
class ClosingRequestByFactFinder
{
    #[FetchableObject(targetEntity: FactFindingAssignment::class, joinColumnName: "FactFindingAssignment_id")]
    #[ManyToOne(targetEntity: FactFindingAssignment::class, inversedBy: "closingRequestByFactFinders", fetch: "LAZY")]
    #[JoinColumn(name: "FactFindingAssignment_id", referencedColumnName: "id")]
    protected FactFindingAssignment $factFindingAssignment;

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected DateTimeImmutable $createdTime;

    #[Column(type: "string", enumType: ManagementApprovalStatus::class)]
    protected ManagementApprovalStatus $status;

    #[Column(type: "integer", nullable: false)]
    protected int $transactionValue;

    #[Column(type: "text", nullable: true)]
    protected ?string $note;

    #[Column(type: "text", nullable: true)]
    protected ?string $remark;

    public function getStatus(): ManagementApprovalStatus
    {
        return $this->status;
    }

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

    public function accept(ClosingRequestByFactFinderData $data): void
    {
        $this->assertWaitingForApproval();
        $this->status = ManagementApprovalStatus::APPROVED;
        $this->remark = $data->remark;
        $this->factFindingAssignment->closeAssignment();
    }

    public function reject(ClosingRequestByFactFinderData $data): void
    {
        $this->assertWaitingForApproval();
        $this->status = ManagementApprovalStatus::REJECTED;
        $this->remark = $data->remark;
    }
    
    //
    public function assertBelongsToManager(Manager $manager): void
    {
        if (!$this->factFindingAssignment->belongsToManager($manager)) {
            throw RegularException::forbidden('closing request does not belongs to manager');
        }
    }
}
