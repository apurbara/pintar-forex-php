<?php

namespace Resources\Event;

use Tests\TestBase;

class ContainEventsTest extends TestBase
{

    protected $container;
    protected $event;
    protected $other;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = new TestableContainEvents();
        $this->event = $this->buildMockOfInterface(EventInterface::class);
        $this->other = $this->buildMockOfInterface(ContainEventsInterface::class);
    }

    protected function recordEvent()
    {
        $this->container->record($this->event);
    }
    public function test_recordEvent_addToRecordedEvent()
    {
        $this->recordEvent();
        $this->assertSame($this->event, $this->container->recordedEvents()[0]);
    }
    
    protected function aggregateEventContainer()
    {
        $this->container->aggregate($this->other);
    }
    public function test_aggregateEventContainer_addToAggregatedContainers()
    {
        $this->aggregateEventContainer();
        $this->assertSame($this->other, $this->container->aggregatedEventContainers()[0]);
    }
    
    protected function pullRecordedEvents()
    {
        $this->container->record($this->event);
        $this->container->aggregate($this->other);
        return $this->container->pullRecordedEvents();
    }
    public function test_pullRecordedEvents_returnAllRecordedEvents()
    {
        $this->assertEquals([$this->event], $this->pullRecordedEvents());
    }
    public function test_pullRecordedEvents_clearRecordedEvents()
    {
        $this->pullRecordedEvents();
        $this->assertEmpty($this->container->recordedEvents());
    }
    public function test_pullRecordedEvents_appendAggregatedEvents()
    {
        $this->other->expects($this->once())
                ->method('pullRecordedEvents')
                ->willReturn([$otherEvents = $this->buildMockOfClass(EventInterface::class)]);
        $this->assertEquals([$this->event, $otherEvents], $this->pullRecordedEvents());
    }

}

class TestableContainEvents
{

    use ContainEventsTrait;

    public function recordedEvents()
    {
        return $this->recordedEvents;
    }

    public function aggregatedEventContainers()
    {
        return $this->childrenContainEvents;
    }

    public function record($event)
    {
        $this->recordEvent($event);
    }

    public function aggregate($other)
    {
        $this->storeChildContainEvents($other);
    }

}
