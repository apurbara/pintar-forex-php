<?php

namespace Resources\Event;

class CommonEvent implements EventInterface
{

    /**
     * 
     * @var string
     */
    protected $name;

    /**
     * 
     * @var string
     */
    protected $id;

    public function __construct(string $name, string $id)
    {
        $this->name = $name;
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getId(): string
    {
        return $this->id;
    }

}
