<?php

namespace Manager\Domain\Model\Manager\Sales;

use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Manager\Domain\Model\Manager\Sales\CustomerAssignment\SalesActivitySchedule;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObjectList;
use Shared\Domain\Enum\CustomerAssignmentStatus;
use Shared\Domain\Enum\SalesActivityScheduleStatus;


#[Entity]
class CustomerAssignment
{

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected DateTimeImmutable $createdTime;

    #[FetchableObjectList(targetEntity: SalesActivitySchedule::class, joinColumnName: "CustomerAssignment_id",
                paginationRequired: false)]
    #[OneToMany(targetEntity: SalesActivitySchedule::class, mappedBy: "customerAssignment", fetch: "EXTRA_LAZY")]
    protected Collection $salesActivitySchedules;

    public function getStatus(): CustomerAssignmentStatus
    {
        return $this->status;
    }

    public function __construct(string $id)
    {
        $this->id = $id;
        $this->createdTime = new DateTimeImmutable();
    }

    public function cancelAllActiveSchedule(): void
    {
        $criteria = Criteria::create()
                ->andWhere(Criteria::expr()->eq('status', SalesActivityScheduleStatus::SCHEDULED));
        foreach ($this->salesActivitySchedules->matching($criteria)->getIterator() as $scheduledActivity) {
            $scheduledActivity->cancelBySystem();
        }
    }
}
