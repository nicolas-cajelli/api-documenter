<?php

namespace Documenter;

class Definition
{
    protected $name;
    /**
     * @var array
     */
    private $properties;
    
    public function __construct(string $name, array $properties)
    {
        $this->name = $name;
        $this->properties = $properties;
    }
    
    public function __toString()
    {
        return $this->name;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * @return array
     */
    public function getProperties(): array
    {
        return $this->properties;
    }
}