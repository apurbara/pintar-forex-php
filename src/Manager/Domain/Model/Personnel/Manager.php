<?php

namespace Manager\Domain\Model\Personnel;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Manager\Domain\Model\Personnel;
use Manager\Domain\Model\Personnel\Manager\Sales;
use Manager\Domain\Service\CustomerAssignmentPriorityCalculatorService;
use Manager\Domain\Task\ManagerTask;
use Manager\Infrastructure\Persistence\Doctrine\Repository\DoctrineManagerRepository;
use Resources\Exception\RegularException;
use SharedContext\Domain\Enum\SalesType;

#[Entity(repositoryClass: DoctrineManagerRepository::class)]
class Manager
{

    #[ManyToOne(targetEntity: Personnel::class)]
    #[JoinColumn(name: "Personnel_id", referencedColumnName: "id")]
    protected Personnel $personnel;

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $disabled;
    
    #[OneToMany(targetEntity: Sales::class, mappedBy: "manager", fetch: 'EXTRA_LAZY')]
    protected Collection $salesCollection;

    public function getId(): string
    {
        return $this->id;
    }

    protected function __construct()
    {
        
    }

    //
    public function executeTask(ManagerTask $task, $payload): void
    {
        if ($this->disabled) {
            throw RegularException::forbidden('only active manager can make this request');
        }
        $task->executeByManager($this, $payload);
    }

    //
    public function registerActiveFreelanceSales(CustomerAssignmentPriorityCalculatorService $assignmentPriorityCalculator): void
    {
        $criteria = Criteria::create()
                ->andWhere(Criteria::expr()->eq('disabled', false))
                ->andWhere(Criteria::expr()->eq('type', SalesType::FREELANCE));
        foreach ($this->salesCollection->matching($criteria)->getIterator() as $sales) {
            $assignmentPriorityCalculator->registerSales($sales);
        }
    }
}
