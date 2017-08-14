<?php

namespace Documenter\Responses;

use Documenter\Definition;

class ErrorResponse extends Response
{
    public function __construct(int $statusCode, Definition $definition = null, string $description = '')
    {
        parent::__construct($statusCode, $description);
    }
    
}
