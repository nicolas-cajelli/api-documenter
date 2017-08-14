<?php

namespace Documenter\Responses;

use Documenter\Definition;

class ObjectResponse extends Response
{
    /**
     * @var Definition
     */
    private $definition;
    
    public function __construct(int $statusCode, Definition $definition, string $description = '')
    {
        parent::__construct($statusCode, $description);
        $this->definition = $definition;
    }
    
    /**
     * @return Definition
     */
    public function getDefinition(): Definition
    {
        return $this->definition;
    }
    
}
