<?php

namespace Resources\Event;

use Tests\TestBase;

class DispatcherTest extends TestBase
{
    protected $dispatcher;
    protected $listener, $eventName = 'event-name';
    protected $event;
    protected $containEvents;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->dispatcher = new TestableDispatcher();
        $this->listener = $this->buildMockOfInterface(ListenerInterface::class);
        
        $this->event = $this->buildMockOfInterface(EventInterface::class);
        $this->event->expects($this->any())
                ->method('getName')
                ->willReturn($this->eventName);
        
        $this->containEvents = $this->buildMockOfInterface(ContainEventsInterface::class);
        $this->containEvents->expects($this->any())
                ->method('pullRecordedEvents')
                ->willReturn([$this->event]);
    }
    
    //
    protected function addImmediateListener()
    {
        $this->dispatcher->addImmediateListener($this->eventName, $this->listener);
    }
    public function test_addImmediateListener_addLisstenerToImmediateList()
    {
        $this->addImmediateListener();
        $this->assertSame($this->listener, $this->dispatcher->immediateListeners[$this->eventName][0]);
    }
    public function test_addImmediateListener_listenerAlreadyListedToListenSameEvent_preventDuplicate()
    {
        $this->dispatcher->immediateListeners[$this->eventName][] = $this->listener;
        $this->addImmediateListener();
        $this->assertEquals(1, count($this->dispatcher->immediateListeners[$this->eventName]));
    }
    
    //
    protected function addTransactionalListener()
    {
        $this->dispatcher->addTransactionalListener($this->eventName, $this->listener);
    }
    public function test_addTransactionalListener_addLisstenerToTransactionalList()
    {
        $this->addTransactionalListener();
        $this->assertSame($this->listener, $this->dispatcher->transactionalListeners[$this->eventName][0]);
    }
    public function test_addTransactionalListener_listenerAlreadyListedToListenSameEvent_preventDuplicate()
    {
        $this->dispatcher->transactionalListeners[$this->eventName][] = $this->listener;
        $this->addTransactionalListener();
        $this->assertEquals(1, count($this->dispatcher->transactionalListeners[$this->eventName]));
    }
    
    //
    protected function addAsynchronousListener()
    {
        $this->dispatcher->addAsynchronousListener($this->eventName, $this->listener);
    }
    public function test_addAsynchronousListener_addLisstenerToAsynchronousList()
    {
        $this->addAsynchronousListener();
        $this->assertSame($this->listener, $this->dispatcher->asynchronousListeners[$this->eventName][0]);
    }
    public function test_addAsynchronousListener_listenerAlreadyListedToListenSameEvent_preventDuplicate()
    {
        $this->dispatcher->asynchronousListeners[$this->eventName][] = $this->listener;
        $this->addAsynchronousListener();
        $this->assertEquals(1, count($this->dispatcher->asynchronousListeners[$this->eventName]));
    }
    
    //
    protected function dispatchEvent()
    {
        $this->dispatcher->immediateListeners[$this->eventName][] = $this->listener;
        $this->dispatcher->dispatchEvent($this->event);
    }
    public function test_dispatchEvent_askAllImmediateListenerToHandleEvent()
    {
        $this->listener->expects($this->once())
                ->method('handle')
                ->with($this->event);
        $this->dispatchEvent();
    }
    public function test_dispatchEvent_recordDispatchedEvent()
    {
        $this->dispatchEvent();
        $this->assertSame($this->event, $this->dispatcher->dispatchedEvents[0]);
    }
    public function test_dispatchEvent_noListeingImmediateListener_void()
    {
        $this->dispatcher->dispatchEvent($this->event);
        $this->assertSame($this->event, $this->dispatcher->dispatchedEvents[0]);
        $this->markAsSuccess();
    }
    
    //
    protected function dispatchEventContainer()
    {
        $this->dispatcher->immediateListeners[$this->eventName][] = $this->listener;
        $this->dispatcher->dispatchEventContainer($this->containEvents);
    }
    public function test_dispatchEventContainer_dispatchAllPulledEvents()
    {
        $this->listener->expects($this->once())
                ->method('handle')
                ->with($this->event);
        $this->dispatchEventContainer();
    }
    
    //
    protected function publishTransactional()
    {
        $this->dispatcher->dispatchedEvents[] = $this->event;
        $this->dispatcher->transactionalListeners[$this->eventName][] = $this->listener;
        $this->dispatcher->publishTransactional();
    }
    public function test_publishTransactional_handleAllListeningListenerInTransactionalList()
    {
        $this->listener->expects($this->once())
                ->method('handle')
                ->with($this->event);
        $this->publishTransactional();
    }
    public function test_publish_noEventListenerInTransactionalList()
    {
        $this->dispatcher->dispatchedEvents[] = $this->event;
        $this->dispatcher->publishTransactional();
        $this->markAsSuccess();
    }
    
    //
    protected function publishAsynchronous()
    {
        $this->dispatcher->dispatchedEvents[] = $this->event;
        $this->dispatcher->asynchronousListeners[$this->eventName][] = $this->listener;
        $this->dispatcher->publishAsynchronous();
    }
    public function test_publishAsynchronous_handleAllListeningListenerInAsynchronousList()
    {
        $this->listener->expects($this->once())
                ->method('handle')
                ->with($this->event);
        $this->publishAsynchronous();
    }
    public function test_publish_noEventListenerInAsynchronousList()
    {
        $this->dispatcher->dispatchedEvents[] = $this->event;
        $this->dispatcher->publishAsynchronous();
        $this->markAsSuccess();
    }
}

class TestableDispatcher extends Dispatcher
{
    public $dispatchedEvents = [];
    public $immediateListeners = [];
    public $transactionalListeners = [];
    public $asynchronousListeners = [];
}
