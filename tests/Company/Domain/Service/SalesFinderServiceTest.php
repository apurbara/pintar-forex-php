<?php

namespace Company\Domain\Service;

use Tests\TestBase;

class SalesFinderServiceTest extends TestBase
{
    protected $salesRepository;
    protected $service;
    //
    protected $managerId = 'managerId';


    protected function setUp(): void
    {
        parent::setUp();
        $this->salesRepository = $this->buildMockOfClass(SalesRepository::class);
        $this->service = new SalesFinderService($this->salesRepository);
    }
    
    //
    protected function findLeastOccupiedFactFinderBelongsToManager()
    {
        return $this->service->findLeastOccupiedFactFinderBelongsToManager($this->managerId);
    }
    public function test_findLeastOccupiedFactFinderBelongsToManager_returnRepositoryResult()
    {
        $this->salesRepository->expects($this->once())
                ->method('findLeastOccupiedFactFinderBelongsToManager')
                ->with($this->managerId);
        $this->findLeastOccupiedFactFinderBelongsToManager();
    }
    
    //
    protected function findLeastOccupiedStrikerBelongsToManager()
    {
        return $this->service->findLeastOccupiedStrikerBelongsToManager($this->managerId);
    }
    public function test_findLeastOccupiedStrikerBelongsToManager_returnRepositoryResult()
    {
        $this->salesRepository->expects($this->once())
                ->method('findLeastOccupiedStrikerBelongsToManager')
                ->with($this->managerId);
        $this->findLeastOccupiedStrikerBelongsToManager();
    }
}
