<?php

namespace Documenter;

use DocBlockReader\Reader;
use ReflectionClass;

class DefinitionsContainer
{
    protected $definitions = [];
    
    public function getDefinition(string $name) : Definition
    {
        if (! isset($this->definitions[$name])) {
            $properties = [];
            $reflectionClass = new ReflectionClass($name);
            foreach ($reflectionClass->getProperties() as $property) {
                $reader = new Reader($name, $property->getName(), 'property');
                $type = $reader->getParameter('var');
                
                switch (true) {
                    case @class_exists($reflectionClass->getNamespaceName() . '\\' . $type):
                        $properties[$property->getName()] = $this->getDefinition($reflectionClass->getNamespaceName() . '\\' . $type);
                        break;
                    case $type == 'array':
                    case $type == 'integer':
                        $properties[$property->getName()] = $type;
                        break;
                    default:
                        $properties[$property->getName()] = 'string';
                }
                
                
            }
            $this->definitions[$name] = new Definition($name, $properties);
        }
        return $this->definitions[$name];
    }
    
    public function getDefinitions()
    {
        return $this->definitions;
    }
}
