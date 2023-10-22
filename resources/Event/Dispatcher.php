<?php

namespace Resources\Event;

class Dispatcher
{

    /**
     * 
     * @var EventInterface[]
     */
    protected $dispatchedEvents = [];

    /**
     * 
     * @var ListenerInterface[]
     */
    protected $immediateListeners = [];

    /**
     * 
     * @var ListenerInterface[]
     */
    protected $transactionalListeners = [];

    /**
     * 
     * @var ListenerInterface[]
     */
    protected $asynchronousListeners = [];

    public function __construct()
    {
        
    }

    public function addImmediateListener(string $eventName, ListenerInterface $listener): void
    {
        if (isset($this->immediateListeners[$eventName]) 
                && in_array($listener, $this->immediateListeners[$eventName])) {
            return;
        }
        $this->immediateListeners[$eventName][] = $listener;
    }

    public function addTransactionalListener(string $eventName, ListenerInterface $listener): void
    {
        if (isset($this->transactionalListeners[$eventName]) 
                && in_array($listener, $this->transactionalListeners[$eventName])) {
            return;
        }
        $this->transactionalListeners[$eventName][] = $listener;
    }

    public function addAsynchronousListener(string $eventName, ListenerInterface $listener): void
    {
        if (isset($this->asynchronousListeners[$eventName]) 
                && in_array($listener, $this->asynchronousListeners[$eventName])) {
            return;
        }
        $this->asynchronousListeners[$eventName][] = $listener;
    }

    public function dispatchEvent(EventInterface $event): void
    {
        $this->dispatchedEvents[] = $event;
        if (!isset($this->immediateListeners[$event->getName()])) {
            return;
        }
        foreach ($this->immediateListeners[$event->getName()] as $immediateListener) {
            $immediateListener->handle($event);
        }
    }

    public function dispatchEventContainer(ContainEventsInterface $eventContainer): void
    {
        foreach ($eventContainer->pullRecordedEvents() as $event) {
            $this->dispatchEvent($event);
        }
    }

    public function publishTransactional(): void
    {
        foreach ($this->dispatchedEvents as $event) {
            if (isset($this->transactionalListeners[$event->getName()])) {
                foreach ($this->transactionalListeners[$event->getName()] as $listener) {
                    $listener->handle($event);
                }
            }
        }
    }

    public function publishAsynchronous(): void
    {
        foreach ($this->dispatchedEvents as $event) {
            if (isset($this->asynchronousListeners[$event->getName()])) {
                foreach ($this->asynchronousListeners[$event->getName()] as $listener) {
                    $listener->handle($event);
                }
            }
        }
    }
    
}
