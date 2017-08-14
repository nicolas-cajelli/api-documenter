<?php

namespace Documenter\Responses;

use Documenter\Definition;

class ArrayResponse extends Response
{
    /**
     * @var Definition
     */
    private $elementsDefinition;
    
    public function __construct(int $statusCode, Definition $elementsDefinition, $description = '')
    {
        parent::__construct($statusCode, $description);
        $this->elementsDefinition = $elementsDefinition;
    }
    
    /**
     * @return Definition
     */
    public function getElementsDefinition(): Definition
    {
        return $this->elementsDefinition;
    }
    
}
